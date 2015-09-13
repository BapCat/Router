<?php namespace BapCat\Router;

use BapCat\Values\HttpMethod;
use BapCat\Values\HttpStatusCode;

use Exception;
use JsonSerializable;

abstract class RoutingException extends Exception implements JsonSerializable {
  private $status;
  private $method;
  private $route;
  
  public function __construct(HttpStatusCode $status, HttpMethod $method, $route) {
    $this->status = $status;
    $this->method = $method;
    $this->route  = $route;
  }
  
  public function jsonSerialize() {
    return [
      'status' => $this->status->code, //TODO: JsonSerializable these
      'method' => (string)$this->method,
      'route'  => $this->route
    ];
  }
  
  public function getStatus() {
    return $this->status;
  }
  
  public function getMethod() {
    return $this->method;
  }
  
  public function getRoute() {
    return $this->route;
  }
}
