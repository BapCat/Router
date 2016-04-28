<?php namespace BapCat\Router;

use BapCat\Values\HttpMethod;
use BapCat\Values\HttpStatusCode;

class RouteNotFoundException extends RoutingException {
  public function __construct(HttpMethod $method, $route, $previous = null) {
    parent::__construct(HttpStatusCode::NOT_FOUND(), $method, $route, $previous);
  }
}
