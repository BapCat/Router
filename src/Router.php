<?php namespace BapCat\Router;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Remodel\Entity;
use BapCat\Remodel\EntityNotFoundException;
use BapCat\Request\Request;
use BapCat\Values\HttpMethod;

use TRex\Reflection\CallableReflection;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use TypeError;

class Router {
  private $ioc;
  
  private $routes = [];
  
  public function __construct(Ioc $ioc) {
    $this->ioc = $ioc;
  }
  
  public function get($alias, $route, callable $action) {
    $this->addRoute(HttpMethod::GET(), $alias, $route, $action);
  }
  
  public function post($alias, $route, callable $action) {
    $this->addRoute(HttpMethod::POST(), $alias, $route, $action);
  }
  
  public function put($alias, $route, callable $action) {
    $this->addRoute(HttpMethod::PUT(), $alias, $route, $action);
  }
  
  public function delete($alias, $route, callable $action) {
    $this->addRoute(HttpMethod::DELETE(), $alias, $route, $action);
  }
  
  //TODO: alias
  private function addRoute(HttpMethod $method, $alias, $route, $action) {
    $segments = explode('/', $this->trimSlashes($route));
    
    $sub_route = &$this->routes;
    foreach($segments as $index => $segment) {
      $sub_route = &$sub_route[$segment];
      
      if($index === count($segments) - 1) {
        $sub_route['__actions'][(string)$method] = $action;
      }
    }
  }
  
  public function findActionByRoute(HttpMethod $method, $route) {
    $segments = explode('/', $this->trimSlashes($route));
    
    $dynamic = [];
    
    $sub_route = &$this->routes;
    foreach($segments as $segment) {
      if(!array_key_exists($segment, $sub_route)) {
        $real_segment = $this->findDynamicSubroute($sub_route);
        
        if($real_segment === null) {
          throw new RouteNotFoundException($method, $route);
        }
        
        $dynamic[substr($real_segment, 1)] = $segment;
        $segment = $real_segment;
      }
      
      $sub_route = &$sub_route[$segment];
    }
    
    if(!array_key_exists('__actions', $sub_route)) {
      throw new RouteNotFoundException($method, $route);
    }
    
    if(!array_key_exists((string)$method, $sub_route['__actions'])) {
      throw new MethodNotAllowedException($method, $route);
    }
    
    return [
      'action'  => $sub_route['__actions'][(string)$method],
      'dynamic' => $dynamic
    ];
  }
  
  public function routeRequestToAction(Request $request) {
    try {
      $action = $this->findActionByRoute($request->method, $request->uri);
      
      $params = $this->getCallableTypeHints($action['action']);
      
      try {
        $args = $this->makeArguments($params, $action['dynamic'], $request);
        
        $response = $this->ioc->call($action['action'], [$request] + $args);
      } catch(TypeError $err) {
        throw new RouteNotFoundException($request->method, $request->uri);
      } catch(EntityNotFoundException $ex) {
        throw new RouteNotFoundException($request->method, $request->uri);
      } catch(InvalidArgumentException $ex) {
        throw new RouteNotFoundException($request->method, $request->uri);
      }
      
      if(($request->is_json && $response instanceof JsonSerializable) || is_array($response)) {
        header('Content-Type: application/json');
        return json_encode($response);
      }
      
      return $response;
    } catch(RoutingException $ex) {
      if($request->is_json) {
        return json_encode($ex);
      }
      
      throw $ex;
    }
  }
  
  private function getCallableTypeHints(callable $action) {
    $reflector = new CallableReflection($action);
    $method = $reflector->getReflector();
    $params = $method->getParameters();
    
    $mapped = [];
    foreach($params as $param) {
      if($param->getClass() !== null) {
        $mapped[$param->getName()] = ['name' => $param->getClass()->getName(), 'optional' => $param->isOptional()];
      }
    }
    
    return $mapped;
  }
  
  private function makeArguments(array $params, array $dynamic, Request $request) {
    $args = [];
    
    foreach($dynamic as $name => $value) {
      if(array_key_exists($name, $params)) {
        $source = $name;
        $class = new ReflectionClass($params[$name]['name']);
        $is_entity = $class->implementsInterface(Entity::class);
        
        if($is_entity) {
          $params[$name . '_id']['name'] = $params[$name]['name'] . 'Id';
          $name .= '_id';
        }
        
        $args[$source] = $this->ioc->make($params[$name]['name'], [$value]);
        
        if($is_entity) {
          $repo = $this->ioc->make($params[$source]['name'] . 'Repository');
          $args[$source] = $repo->withId($args[$source])->first();
        }
      } else {
        $args[$name] = $value;
      }
    }
    
    return $args;
  }
  
  private function trimSlashes($route) {
    return trim($route, '/');
  }
  
  private function findDynamicSubroute(array $routes) {
    foreach(array_keys($routes) as $segment) {
      if(strlen($segment) !== 0) {
        if($segment[0] === ':') {
          return $segment;
        }
      }
    }
    
    return null;
  }
  
  public function dumpRoutes() {
    var_export($this->routes);
  }
}
