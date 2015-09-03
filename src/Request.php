<?php namespace BapCat\Router;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\HttpMethod;

use ArrayIterator;

class Request {
  use PropifierTrait;
  
  private $method;
  private $uri;
  private $host;
  
  private $query;
  private $request;
  
  public static function fromGlobals() {
    if(php_sapi_name() === 'cli') {
      throw new InvalidStateException('Requests can not be instantiated from globals in CLI mode');
    }
    
    return new static(
      HttpMethod::memberByKey($_SERVER['REQUEST_METHOD'], false),
      strtok($_SERVER['REQUEST_URI'], '?'),
      $_SERVER['HTTP_HOST'],
      $_GET,
      $_POST
    );
  }
  
  public function __construct(HttpMethod $method, $uri, $host, array $query = [], array $request = []) {
    $this->method  = $method;
    $this->uri     = $uri;
    $this->host    = $host;
    $this->query   = $query;
    $this->request = $request;
  }
  
  protected function getMethod() {
    return $this->method;
  }
  
  protected function getUri() {
    return $this->uri;
  }
  
  protected function getHost() {
    return $this->host;
  }
  
  public function hasQuery($key) {
    return isset($this->query[$key]);
  }
  
  public function query($key, $default = null) {
    if($this->hasQuery($key)) {
      return $this->query[$key];
    }
    
    return $default;
  }
  
  protected function getQuery($key) {
    if(!$this->hasQuery($key)) {
      throw new NoSuchValueException("Query does not contain [$key]");
    }
    
    return $this->query[$key];
  }
  
  protected function itrQuery() {
    return new ArrayIterator($this->query);
  }
  
  public function hasRequest($key) {
    return isset($this->request[$key]);
  }
  
  public function request($key, $default = null) {
    if($this->hasRequest($key)) {
      return $this->request[$key];
    }
    
    return $default;
  }
  
  protected function getRequest($key) {
    if(!$this->hasRequest($key)) {
      throw new NoSuchValueException("Request does not contain [$key]");
    }
    
    return $this->request[$key];
  }
  
  protected function itrRequest() {
    return new ArrayIterator($this->request);
  }
}
