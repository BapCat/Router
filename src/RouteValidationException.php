<?php namespace BapCat\Router;

use BapCat\Values\HttpMethod;
use BapCat\Values\HttpStatusCode;

class RouteValidationException extends RoutingException {
  private $problems;
  
  public function __construct(HttpMethod $method, $route, array $problems) {
    parent::__construct(HttpStatusCode::BAD_REQUEST(), $method, $route);
    
    $this->problems = $problems;
  }
  
  public function jsonSerialize() {
    return array_merge(parent::jsonSerialize(), [
      'problems' => $this->problems
    ]);
  }
  
  public function getProblems() {
    return $this->problems;
  }
}
