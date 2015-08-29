<?php namespace BapCat\Router;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\HttpMethod;

class Request {
  use PropifierTrait;
  
  private $uri;
  private $method;
  private $host;
  
  private $query;
  private $request;
  
  public static function fromGlobals() {
    if(php_sapi_name() === 'cli') {
      throw new InvalidStateException('Requests can not be instantiated in CLI mode');
    }
    
    return new static(
      $_SERVER['REQUEST_URI'],
      $_SERVER['REQUEST_METHOD'],
      $_SERVER['HTTP_HOST'],
      $_GET,
      $_POST
    );
  }
  
  public function __construct($uri, HttpMethod $method, $host, array $query = [], array $request = []) {
    $this->uri     = $uri;
    $this->method  = 'aaaaaaaaa';//$method;
    $this->host    = $host;
    $this->query   = $query;
    $this->request = $request;
  }
  
  protected function getUri() {
    return $this->uri;
  }
  
  protected function getMethod() {
    return $this->method;
  }
  
  protected function getHost() {
    return $this->host;
  }
  
  protected function getQuery($key) {
    return $this->query[$key];
  }
  
  protected function getRequest($key) {
    return $this->request[$key];
  }
}
