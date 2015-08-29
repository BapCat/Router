<?php

use BapCat\Phi\Phi;
use BapCat\Router\Router;
use BapCat\Values\HttpMethod;

class RouterTest extends PHPUnit_Framework_TestCase {
  private $router;
  
  public function setUp() {
    $this->router = new Router(Phi::instance());
  }
  
  public function testAddingAndFindingRegularRoutes() {
    foreach(['get', 'post', 'put', 'delete'] as $method) {
      $fn = function() { };
      $this->router->$method($method, $method, $fn);
      
      $this->assertSame($fn, $this->router->findActionByRoute(HttpMethod::memberByKey($method, false), $method));
    }
  }
}
