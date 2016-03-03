<?php namespace BapCat\Router;

use BapCat\Values\HttpMethod;
use BapCat\Values\HttpStatusCode;

class MethodNotAllowedException extends RoutingException {
  public function __construct(HttpMethod $method, $route) {
    parent::__construct(HttpStatusCode::METHOD_NOT_ALLOWED(), $method, $route);
  }
}
