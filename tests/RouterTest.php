<?php

use BapCat\Phi\Phi;
use BapCat\Request\Request;
use BapCat\Router\Router;
use BapCat\Values\HttpMethod;

class RouterTest extends PHPUnit_Framework_TestCase {
  private $router;
  
  public function setUp() {
    $ioc = Phi::instance();
    $this->router = new Router($ioc);
  }
  
  public function testAddingAndFindingRegularRoutes() {
    foreach(['get', 'post', 'put', 'delete'] as $method) {
      $fn = function() { };
      $this->router->$method($method, $method, $fn);
      
      $this->assertSame($fn, $this->router->findActionByRoute(HttpMethod::memberByKey($method, false), $method));
    }
  }
  
  public function testRoutingRequest() {
    $request = new Request(HttpMethod::POST(), '/test', 'example.com', [], [], [], ['test' => 'test']);
    
    $called = false;
    $this->router->post('', '/test', function(Request $request) use(&$called) {
      $this->assertEquals('test', $request->input->get('test'));
      $called = true;
    });
    
    $this->router->routeRequestToAction($request);
    
    $this->assertTrue($called);
  }
}
