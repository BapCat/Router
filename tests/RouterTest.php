<?php

require_once __DIR__ . '/stubs/TestController.php';

use BapCat\Facade\Facade;
use BapCat\Persist\Drivers\Filesystem\FilesystemDriver;
use BapCat\Phi\Phi;
use BapCat\Router\Request;
use BapCat\Router\Router;
use BapCat\Router\RouterTemplateFinder;
use BapCat\Tailor\Compilers\Compiler;
use BapCat\Tailor\Compilers\PhpCompiler;
use BapCat\Tailor\Tailor;
use BapCat\Tailor\TemplateFinder;
use BapCat\Values\HttpMethod;
use BapCat\Values\Text;

class RouterTest extends PHPUnit_Framework_TestCase {
  private $router;
  
  public function setUp() {
    $ioc = Phi::instance();
    Facade::setIoc($ioc);
    
    $filesystem = new FilesystemDriver(__DIR__ . '/../cache');
    $compiled   = $filesystem->get('/');
    
    $finder = new RouterTemplateFinder($compiled);
    
    $this->router = new Router($ioc, $finder);
  }
  
  public function testAddingAndFindingRegularRoutes() {
    foreach(['get', 'post', 'put', 'delete'] as $method) {
      $fn = function() { };
      $this->router->$method($method, $method, $fn);
      
      $this->assertSame($fn, $this->router->findActionByRoute(HttpMethod::memberByKey($method, false), $method));
    }
  }
  
  public function testRoutingRequest() {
    $request = new Request(HttpMethod::POST(), '/test', 'example.com', [], ['test' => 'test']);
    
    $called = false;
    $this->router->post('', '/test', function(Text $test) use(&$called) {
      $this->assertEquals('test', $test);
      $called = true;
    });
    
    $this->router->routeRequestToAction($request);
    
    $this->assertTrue($called);
  }
  
  public function testControllerGeneration() {
    $this->router->controller(\Test\TestController::class);
    $this->assertTrue(\TestController::test());
  }
}
