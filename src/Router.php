<?php namespace BapCat\Router;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Values\HttpMethod;

use TRex\Reflection\CallableReflection;

class Router {
  private $ioc;
  
  private $mappings = [];
  private $routes   = [];
  
  public function __construct(Ioc $ioc) {
    $this->ioc = $ioc;
  }
  
  public function map($param, $alias, callable $callback) {
    $this->mappings[$param] = ['alias' => $alias, 'callback' => $callback];
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
      
      if($index == count($segments) - 1) {
        $sub_route["__$method"] = $action;
      }
    }
  }
  
  public function findActionByRoute(HttpMethod $method, $route) {
    $segments = explode('/', $this->trimSlashes($route));
    $segments[] = "__$method";
    
    $sub_route = &$this->routes;
    foreach($segments as $index => $segment) {
      if(!array_key_exists($segment, $sub_route)) {
        $segment = $this->findDynamicSubroute($sub_route);
        
        if($segment === null) {
          throw new RouteNotFoundException($route);
        }
      }
      
      $sub_route = &$sub_route[$segment];
    }
    
    return $sub_route;
  }
  
  public function routeRequestToAction(Request $request) {
    $action = $this->findActionByRoute($request->method, $this->trimSlashes($request->uri));
    
    $params = $this->getCallableTypeHints($action);
    $args = [];
    
    foreach($params as $name => $type) {
      if(!array_key_exists($name, $this->mappings)) {
        $args[$name] = $this->ioc->make($params[$name], [$request->input[$name]]);
      } else {
        $mapping = $this->mappings[$name];
        
        $callback_params = $this->getCallableTypeHints($mapping['callback']);
        $callback_args = [];
        
        foreach($callback_params as $callback_name => $callback_type) {
          $callback_args[$callback_name] = $this->ioc->make($callback_params[$callback_name], [$request->input[$callback_name]]);
        }
        
        $args[$name] = $this->ioc->call($mapping['callback'], $callback_args);
      }
    }
    
    return $this->ioc->call($action, $args);
  }
  
  private function getCallableTypeHints(callable $action) {
    $reflector = new CallableReflection($action);
    $method = $reflector->getReflector();
    $params = $method->getParameters();
    
    $mapped = [];
    foreach($params as $param) {
      if($param->getClass() !== null) {
        $mapped[$param->getName()] = $param->getClass()->getName();
      }
    }
    
    return $mapped;
  }
  
  private function trimSlashes($route) {
    return trim($route, '/');
  }
  
  private function findDynamicSubroute(array $routes) {
    foreach($routes as $segment => $subroutes) {
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
