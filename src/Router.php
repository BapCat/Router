<?php namespace BapCat\Router;

use BapCat\Values\HttpMethod;

class Router {
  private $routes = [
    '' => []
  ];
  
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
    $segments = explode('/', $this->trimLeadingSlash($route));
    
    $sub_route = &$this->routes;
    foreach($segments as $index => $segment) {
      $sub_route = &$sub_route[$segment];
      
      if($index == count($segments) - 1) {
        $sub_route["__$method"] = $action;
      }
    }
  }
  
  public function findActionForRoute(HttpMethod $method, $route) {
    $segments = explode('/', $this->trimLeadingSlash($route));
    $segments[] = "__$method";
    
    $sub_route = &$this->routes;
    foreach($segments as $index => $segment) {
      if(!array_key_exists($segment, $sub_route)) {
        throw new RouteNotFoundException($route);
      }
      
      $sub_route = &$sub_route[$segment];
    }
    
    return $sub_route;
  }
  
  private function trimLeadingSlash($route) {
    if(strlen($route) != 0) {
      if($route[0] === '/') {
        return substr($route, 1);
      }
    }
    
    return $route;
  }
  
  public function dumpRoutes() {
    var_export($this->routes);
  }
}
