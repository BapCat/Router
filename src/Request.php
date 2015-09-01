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
      throw new InvalidStateException('Requests can not be instantiated from globals in CLI mode');
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
    $this->method  = $method;
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
    if(!$this->hasQuery($key)) {
      throw new NoSuchValueException("Query does not contain [$key]");
    }
    
    return $this->query[$key];
  }
  
  protected function getRequest($key) {
    if(!$this->hasRequest($key)) {
      throw new NoSuchValueException("Request does not contain [$key]");
    }
    
    return $this->request[$key];
  }
  
  public function hasQuery($key) {
    return isset($this->query[$key]);
  }
  
  public function hasRequest($key) {
    return isset($this->request[$key]);
  }
  
  public function query($key, $default = null) {
    if($this->hasQuery($key)) {
      return $this->query[$key];
    }
    
    return $default;
  }
  
  public function request($key, $default = null) {
    if($this->hasRequest($key)) {
      return $this->request[$key];
    }
    
    return $default;
  }
}
