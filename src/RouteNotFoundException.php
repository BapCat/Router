<?php namespace BapCat\Router;

use Exception;

class RouteNotFoundException extends Exception {
  private $route;
  
  public function __construct($route) {
    $this->route = $route;
  }
  
  public function getRoute() {
    return $this->route;
  }
}
