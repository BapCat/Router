<?php namespace BapCat\Router;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Values\HttpMethod;

use TRex\Reflection\CallableReflection;

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
    $params = $this->getActionTypeHints($action);
    
    $args = [];
    foreach($request->request as $name => $value) {
      if(array_key_exists($name, $params)) {
        $args[$name] = $this->ioc->make($params[$name], [$value]);
      }
    }
    
    return $this->ioc->call($action, $args);
  }
  
  private function getActionTypeHints(callable $action) {
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
