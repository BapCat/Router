<?php

use BapCat\Router\Request;
use BapCat\Values\HttpMethod;

class RequestTest extends PHPUnit_Framework_TestCase {
  public function testAccessors() {
    $uri    = '/test';
    $method = HttpMethod::POST();
    $host   = 'example.com';
    $query  = [1];
    $post   = [2];
    
    $request = new Request($uri, $method, $host, $query, $post);
    
    //$this->assertSame($uri, $request->uri);
    $this->assertSame($method, $request->method);
    //$this->assertSame($host, $request->host);
    //$this->assertSame($query[0], $request->query[0]);
    //$this->assertSame($post[0], $request->request[0]);
  }
}
