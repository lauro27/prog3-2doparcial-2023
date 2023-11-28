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
require_once './middlewares/AuthMW.php';
require_once './middlewares/Logger.php';
require_once './util/AuthJWT.php';

require_once './controllers/ClienteController.php';
require_once './controllers/ReservaController.php';
require_once './controllers/LoginController.php';
require_once './controllers/UsuarioController.php';
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
$app->post('/login[/]', \LoginController::class . ':IniciarSesion')
    ->add(new Logger("Inicio de sesion"));

$app->post('/usuario[/]', \UsuarioController::class . ':CargarUno')
    ->add(new Logger("Creación de usuario"))
    ->add(\AuthMW::class . ':LoginGerente');

$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ClienteController::class . ':CargarUno')
        ->add(new Logger("Carga de cliente"))
        ->add(\AuthMW::class . ':LoginGerente');//PUNTO 1-B y 8
    $group->post('/traer[/]', \ClienteController::class . ':TraerUno')
        ->add(new Logger("Busqueda de un cliente"))
        ->add(\AuthMW::class . ':LoginRecepcionistaCliente');//PUNTO 2
    $group->put('[/]', \ClienteController::class . ':ModificarUno')
        ->add(new Logger("Modificación de cliente"))
        ->add(\AuthMW::class . ':LoginGerente');//PUNTO 5
    $group->delete('[/]', \ClienteController::class . ':BorrarUno')
        ->add(new Logger("Borrado de cliente"))
        ->add(\AuthMW::class . ':LoginGerente');//PUNTO 9
  });

$app->group('/reservas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ReservaController::class . ':CargarUno')
        ->add(new Logger("Carga reserva"))
        ->add(\AuthMW::class . ':LoginRecepcionistaCliente');//PUNTO 3
    $group->get('/{consulta}[/]', \ReservaController::class . ':TraerTodos')
        ->add(new Logger("Busqueda de reservas"))
        ->add(\AuthMW::class . ':LoginRecepcionistaCliente');//PUNTO 4 y 10 - incompleto
    $group->post('/cancelar[/]', \ReservaController::class . ':BorrarUno')
        ->add(new Logger("Cancelación de reserva"))
        ->add(\AuthMW::class . ':LoginRecepcionistaCliente');//PUNTO 6
    $group->post('/ajustar[/]', \ReservaController::class . ':ModificarUno')
        ->add(new Logger("Ajuste de reserva"))
        ->add(\AuthMW::class . ':LoginRecepcionistaCliente');//PUNTO 7
  });

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "2do Parcial - Lamas"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
