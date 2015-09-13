<?php namespace BapCat\Router;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Remodel\Entity;
use BapCat\Tailor\Compilers\PhpCompiler;
use BapCat\Tailor\Tailor;
use BapCat\Values\HttpMethod;

use TRex\Reflection\CallableReflection;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;

class Router {
  private $ioc;
  
  private $mappings = [];
  private $routes   = [];
  
  public function __construct(Ioc $ioc, RouterTemplateFinder $finder) {
    $this->ioc    = $ioc;
    $this->tailor = $ioc->make(Tailor::class, [$finder, $ioc->make(PhpCompiler::class)]);
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
  
  public function controller($class_name) {
    $parts = explode('\\', $class_name);
    
    $options = [
      'name'       => array_pop($parts),
      'controller' => $class_name
    ];
    
    $this->tailor->bind($options['name'], 'ControllerFacade', $options);
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
  
  //TODO: MethodNotAllowedException
  public function findActionByRoute(HttpMethod $method, $route) {
    $segments = explode('/', $this->trimSlashes($route));
    $segments[] = "__$method";
    
    $sub_route = &$this->routes;
    foreach($segments as $index => $segment) {
      if(!array_key_exists($segment, $sub_route)) {
        $segment = $this->findDynamicSubroute($sub_route);
        
        if($segment === null) {
          throw new RouteNotFoundException($method, $route);
        }
      }
      
      $sub_route = &$sub_route[$segment];
    }
    
    return $sub_route;
  }
  
  public function routeRequestToAction(Request $request) {
    try {
      $action = $this->findActionByRoute($request->method, $this->trimSlashes($request->uri));
      $params = $this->getCallableTypeHints($action);
      
      $args = $this->makeArguments($params, $request);
    } catch(RoutingException $ex) {
      if($request->is_json) {
        return json_encode($ex);
      }
      
      throw $ex;
    }
    
    $response = $this->ioc->call($action, $args);
    
    if($response instanceof JsonSerializable || is_array($response) || $request->is_json) {
      return json_encode($response);
    }
    
    return $response;
  }
  
  private function makeArguments(array $params, Request $request) {
    $args = [];
    $problems = [];
    
    foreach($params as $name => $type) {
      try {
        if(!array_key_exists($name, $this->mappings)) {
          $source = $name;
          $class = new ReflectionClass($params[$name]['name']);
          $is_entity = $class->implementsInterface(Entity::class);
          
          if($is_entity) {
            $params[$name . '_id']['name'] = $params[$name]['name'] . 'Id';
            $name .= '_id';
          }
          
          if($request->hasInput($name)) {
            $args[$source] = $this->ioc->make($params[$name]['name'], [$request->input[$name]]);
            
            if($is_entity) {
              $repo = $this->ioc->make($params[$source]['name'] . 'Repository');
              $args[$source] = $repo->withId($args[$source])->first();
            }
          } else {
            if(!$params[$source]['optional']) {
              throw new MissingInputException($name, $params[$name]['name']);
            }
          }
        } else {
          $mapping = $this->mappings[$name];
          
          $callback_params = $this->getCallableTypeHints($mapping['callback']);
          $callback_args   = $this->makeArguments($callback_params, $request);
          
          $args[$name] = $this->ioc->call($mapping['callback'], $callback_args);
        }
      } catch(InvalidArgumentException $ex) {
        $problems[$name] = $ex->getMessage();
      } catch(EntityNotFoundException $ex) {
        $problems[$name] = $ex->getMessage();
      }
    }
    
    if(count($problems) !== 0) {
      throw new RouteValidationException($request->method, $request->uri, $problems);
    }
    
    return $args;
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
