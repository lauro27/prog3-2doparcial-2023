<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/ClienteController.php';
require_once './controllers/ReservaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

date_default_timezone_set("America/Argentina/Buenos_Aires");

// Routes
$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ClienteController::class . ':CargarUno');//PUNTO 1-B y 8
    $group->post('/traer[/]', \ClienteController::class . ':TraerUno');//PUNTO 2
    $group->put('[/]', \ClienteController::class . ':ModificarUno');//PUNTO 5
    $group->delete('[/]', \ClienteController::class . ':BorrarUno');//PUNTO 9
  });

$app->group('/reservas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ReservaController::class . ':CargarUno');//PUNTO 3
    $group->get('/{consulta}[/]', \ReservaController::class . ':TraerTodos');//PUNTO 4 y 10 - incompleto
    $group->post('/cancelar[/]', \ReservaController::class . ':BorrarUno');//PUNTO 6
    $group->post('/ajustar[/]', \ReservaController::class . ':ModificarUno');//PUNTO 7
  });

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "2do Parcial - Lamas"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
