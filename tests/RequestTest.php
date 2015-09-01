<?php

use BapCat\Router\NoSuchValueException;
use BapCat\Router\Request;
use BapCat\Values\HttpMethod;

class RequestTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->method = HttpMethod::POST();
    $this->uri    = '/test';
    $this->host   = 'example.com';
    $this->query  = [1];
    $this->post   = [2];
    
    $this->request = new Request($this->method, $this->uri, $this->host, $this->query, $this->post);
  }
  
  public function testAccessors() {
    $this->assertSame($this->method, $this->request->method);
    $this->assertSame($this->uri,    $this->request->uri);
    $this->assertSame($this->host,   $this->request->host);
    
    $this->assertSame($this->query[0], $this->request->query  [0]);
    $this->assertSame($this->post [0], $this->request->request[0]);
    
    $this->assertTrue ($this->request->hasQuery(0));
    $this->assertFalse($this->request->hasQuery(1));
    $this->assertTrue ($this->request->hasRequest(0));
    $this->assertFalse($this->request->hasRequest(1));
    
    $this->assertSame($this->query[0], $this->request->query  (0));
    $this->assertSame($this->post [0], $this->request->request(0));
    
    $this->assertNull($this->request->query  (1));
    $this->assertNull($this->request->request(1));
  }
  
  public function testGetQueryThrowsExceptionOnInvalidValues() {
    $this->setExpectedException(NoSuchValueException::class);
    $this->request->query[1];
  }
  
  public function testGetRequestThrowsExceptionOnInvalidValues() {
    $this->setExpectedException(NoSuchValueException::class);
    $this->request->request[1];
  }
}
