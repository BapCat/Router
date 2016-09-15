<?php

use BapCat\Phi\Phi;
use BapCat\Request\RequestFromGlobals;
use BapCat\Router\Router;
use BapCat\Router\RouteNotFoundException;
use BapCat\Values\HttpMethod;
use BapCat\Values\Text;

require __DIR__ . '/../vendor/autoload.php';



$ioc = Phi::instance();

use BapCat\Persist\Drivers\Local\LocalDriver;
use BapCat\Remodel\EntityDefinition;
use BapCat\Remodel\Registry;
use BapCat\Remodel\RemodelConnection;

use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;

// Grab filesystem directories
$persist = new LocalDriver(__DIR__ . '/..');
$cache = $persist->getDirectory('/cache');

$registry = new Registry($ioc, $cache);

$connection = new RemodelConnection(new PDO('mysql:host=localhost;dbname=test', 'root', ''), new MySqlGrammar(), new MySqlProcessor());





use BapCat\Values\Email;

use BapCat\CoolThing\User;
use BapCat\CoolThing\UserId;
use BapCat\CoolThing\UserGateway;
use BapCat\CoolThing\UserRepository;
use BapCat\CoolThing\UserNotFoundException;

$def = new EntityDefinition(User::class);
$def->required('email', Email::class);
$def->optional('name', Text::class);
$def->timestamps();

$registry->register($def);



$user_repo = new UserRepository(Phi::instance(), new UserGateway($connection));

Phi::instance()->bind(UserRepository::class, $user_repo);




$router = new BapCat\Router\Router($ioc);

$router->get('', '/', function() {
  return 'Test';
});

require __DIR__ . '/TestValidatedRequest.php';

$router->get('', '/landing/:site', function(TestValidatedRequest $test, Text $site) {
  if(!$test->validated) {
    echo "Validation failed!<br>\n";
    
    foreach($test->validation_errors as $err) {
      echo "$err<br>\n";
    }
    
    return;
  }
  
  return "Site: {$site}<br>\nID: {$test->id}<br>\nAP: {$test->ap}<br>\nURL: {$test->url}<br>\n";
});

$router->get('', '/user/:user', function(User $user) {
  return $user;
});

$router->get('', '/test', function(Request $request) {
  if(!$request->query->has('test')) {
    return 'You can pass me some text using the \'test\' GET query!';
  }
  
  return $request->query->get('test');
});

$request = new RequestFromGlobals();

$ioc->bind(Request::class, $request);

try {
  echo $router->routeRequestToAction($request);
} catch(RouteNotFoundException $ex) {
  echo 'Route not found: ' . $ex->getRoute() . "\n";
  var_dump($ex);
}
