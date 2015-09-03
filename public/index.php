<?php

use BapCat\Phi\Phi;
use BapCat\Router\Request;
use BapCat\Router\Router;
use BapCat\Router\RouteNotFoundException;

use BapCat\Values\Text;

require __DIR__ . '/../vendor/autoload.php';

$router = new BapCat\Router\Router(Phi::instance());

$router->get('', '/', function() {
  return 'Test';
});

$router->get('', '/test', function(Text $test = null) {
  if($test === null) {
    return 'You can pass me some text using the \'test\' GET query!';
  }
  
  return $test;
});

try {
  echo $router->routeRequestToAction(Request::fromGlobals());
} catch(RouteNotFoundException $ex) {
  echo 'Route not found: ' . $ex->getRoute();
}
