<?php

use BapCat\Phi\Phi;
use BapCat\Router\Request;
use BapCat\Router\Router;
use BapCat\Router\RouteNotFoundException;
use BapCat\Values\HttpMethod;
use BapCat\Values\Text;

require __DIR__ . '/../vendor/autoload.php';



use BapCat\Persist\Drivers\Filesystem\FilesystemDriver;
use BapCat\Tailor\Tailor;
use BapCat\Tailor\PersistTemplateFinder;
use BapCat\Tailor\Compilers\PhpCompiler;
use Illuminate\Database\MySqlConnection;

// Grab filesystem directories
$persist = new FilesystemDriver(__DIR__ . '/..');
$templates = $persist->get('/vendor/bapcat/remodel/templates');
$compiled  = $persist->get('/cache');

// TemplateFinders are able to find and use raw/compiled templates
$finder = new PersistTemplateFinder($templates, $compiled);

// Compilers translate raw templates into compiled ones
$compiler = new PhpCompiler();

// Create an instance of Tailor to actually do the autoloading
$tailor = new Tailor($finder, $compiler);

$connection = new MySqlConnection(new PDO('mysql:host=localhost;dbname=test', 'root', ''), 'test');





use BapCat\Remodel\Registry;
use BapCat\Remodel\EntityDefinition;
use BapCat\Values\Email;

use BapCat\CoolThing\User;
use BapCat\CoolThing\UserId;
use BapCat\CoolThing\UserGateway;
use BapCat\CoolThing\UserRepository;
use BapCat\CoolThing\UserNotFoundException;

$registry = new Registry($tailor);

$def = new EntityDefinition(User::class);
$def->required('email',      Email::class);
$def->optional('first_name', Text::class);
$def->optional('last_name',  Text::class);
$def->virtual('full_email',  Text::class, ['first_name', "' '", 'last_name', "' <'", 'email', "'>'"]);
$def->virtual('full_name',   Text::class, ['first_name', "' '", 'last_name']);

$registry->register($def);



$user_repo = new UserRepository(Phi::instance(), new UserGateway($connection));

Phi::instance()->bind(UserRepository::class, $user_repo);




$router = new BapCat\Router\Router(Phi::instance());

/*$router->map('user', 'user_id', function(UserId $user_id) use($user_repo) {
  return $user_repo->withId($user_id)->first();
});*/

$router->get('', '/', function() {
  return 'Test';
});

$router->get('', '/test', function(Text $test = null) {
  if($test === null) {
    return 'You can pass me some text using the \'test\' GET query!';
  }
  
  return $test;
});

$router->get('', '/user', function(User $user) {
  return $user;
});

//$request = Request::fromGlobals();

$request = new Request(
  HttpMethod::memberByKey($_SERVER['REQUEST_METHOD'], false),
  strtok($_SERVER['REQUEST_URI'], '?'),
  $_SERVER['HTTP_HOST'],
  array_merge(getallheaders(), ['Accept' => 'application/ajax']),
  $_GET
);

try {
  echo $router->routeRequestToAction($request);
} catch(RouteNotFoundException $ex) {
  echo 'Route not found: ' . $ex->getRoute();
}
