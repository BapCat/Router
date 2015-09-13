<?php

use BapCat\Router\NoSuchValueException;
use BapCat\Router\Request;
use BapCat\Values\HttpMethod;

class RequestTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->method  = HttpMethod::POST();
    $this->uri     = '/test';
    $this->host    = 'example.com';
    $this->headers = [];
    $this->input   = [1];
    
    $this->request = new Request($this->method, $this->uri, $this->host, $this->headers, $this->input);
  }
  
  public function testAccessors() {
    $this->assertSame($this->method, $this->request->method);
    $this->assertSame($this->uri,    $this->request->uri);
    $this->assertSame($this->host,   $this->request->host);
    
    $this->assertSame($this->input[0], $this->request->input[0]);
    $this->assertSame($this->input[0], $this->request->input(0));
    
    $this->assertTrue ($this->request->hasInput(0));
    $this->assertFalse($this->request->hasInput(1));
    
    $this->assertNull($this->request->input(1));
  }
  
  public function testGetInputThrowsExceptionOnInvalidValues() {
    $this->setExpectedException(NoSuchValueException::class);
    $this->request->input[1];
  }
}
