<?php

use BapCat\Phi\Phi;
use BapCat\Request\Request;
use BapCat\Router\Router;
use BapCat\Router\RouteNotFoundException;
use BapCat\Values\HttpMethod;
use BapCat\Values\Text;

require __DIR__ . '/../vendor/autoload.php';



$ioc = Phi::instance();

use BapCat\Persist\Drivers\Local\LocalDriver;
use BapCat\Remodel\Registry;
use BapCat\Remodel\EntityDefinition;

use Illuminate\Database\MySqlConnection;

// Grab filesystem directories
$persist = new LocalDriver(__DIR__ . '/..');
$cache = $persist->getDirectory('/cache');

$registry = new Registry($ioc, $cache);

$connection = new MySqlConnection(new PDO('mysql:host=localhost;dbname=test', 'root', ''), 'test');





use BapCat\Values\Email;

use BapCat\CoolThing\User;
use BapCat\CoolThing\UserId;
use BapCat\CoolThing\UserGateway;
use BapCat\CoolThing\UserRepository;
use BapCat\CoolThing\UserNotFoundException;

$def = new EntityDefinition(User::class);
$def->required('email',      Email::class);
$def->optional('first_name', Text::class);
$def->optional('last_name',  Text::class);
$def->virtual('full_email',  Text::class, ['first_name', "' '", 'last_name', "' <'", 'email', "'>'"]);
$def->virtual('full_name',   Text::class, ['first_name', "' '", 'last_name']);

$registry->register($def);



$user_repo = new UserRepository(Phi::instance(), new UserGateway($connection));

Phi::instance()->bind(UserRepository::class, $user_repo);




$router = new BapCat\Router\Router($ioc);

/*$router->map('user', 'user_id', function(UserId $user_id) use($user_repo) {
  return $user_repo->withId($user_id)->first();
});*/

$router->get('', '/', function() {
  return 'Test';
});

require __DIR__ . '/TestValidatedRequest.php';

$router->get('', '/validate', function(TestValidatedRequest $test) {
  if(!$test->validated) {
    return 'BAD';
  }
  
  return 'GOOD';
});

$router->get('', '/user/:user', function($user) {
  return $user;
});

$router->get('', '/test', function(Request $request) {
  if(!$request->query->has('test')) {
    return 'You can pass me some text using the \'test\' GET query!';
  }
  
  return $request->query->get('test');
});

/*$router->get('', '/user', function(User $user) {
  return $user;
});*/

$request = Request::fromGlobals();

$ioc->bind(Request::class, $request);

try {
  echo $router->routeRequestToAction($request);
} catch(RouteNotFoundException $ex) {
  echo 'Route not found: ' . $ex->getRoute();
}
