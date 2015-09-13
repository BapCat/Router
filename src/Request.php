<?php namespace BapCat\Router;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\HttpMethod;

use ArrayIterator;

class Request {
  use PropifierTrait;
  
  private $method;
  private $uri;
  private $host;
  private $headers;
  private $input;
  
  public static function fromGlobals() {
    if(php_sapi_name() === 'cli') {
      throw new InvalidStateException('Requests can not be instantiated from globals in CLI mode');
    }
    
    $method = HttpMethod::memberByKey($_SERVER['REQUEST_METHOD'], false);
    $input  = $method === HttpMethod::GET() ? $_GET : $_POST;
    
    return new static(
      $method,
      strtok($_SERVER['REQUEST_URI'], '?'),
      $_SERVER['HTTP_HOST'],
      getallheaders(),
      $input
    );
  }
  
  public function __construct(HttpMethod $method, $uri, $host, array $headers, array $input = []) {
    $this->method  = $method;
    $this->uri     = $uri;
    $this->host    = $host;
    $this->headers = $headers;
    $this->input   = $input;
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
  
  protected function getIsJson() {
    return $this->header('Accept') === 'application/ajax';
  }
  
  public function hasHeader($key) {
    return isset($this->headers[$key]);
  }
  
  public function header($key, $default = null) {
    if($this->hasHeader($key)) {
      return $this->headers[$key];
    }
    
    return $default;
  }
  
  protected function getHeader($key) {
    if(!$this->hasHeader($key)) {
      throw new NoSuchValueException("Header does not contain [$key]");
    }
    
    return $this->headers[$key];
  }
  
  protected function itrHeader() {
    return new ArrayIterator($this->headers);
  }
  
  public function hasInput($key) {
    return isset($this->input[$key]);
  }
  
  public function input($key, $default = null) {
    if($this->hasInput($key)) {
      return $this->input[$key];
    }
    
    return $default;
  }
  
  protected function getInput($key) {
    if(!$this->hasInput($key)) {
      throw new NoSuchValueException("Input does not contain [$key]");
    }
    
    return $this->input[$key];
  }
  
  protected function itrInput() {
    return new ArrayIterator($this->input);
  }
}
