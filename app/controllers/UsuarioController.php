<?php

use Slim\Psr7\Response;

require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './util/CsvHandler.php';

class UsuarioController extends Usuario
{
    public function CargarUno($request, $handler)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];
        // Creamos el usuario
        $usr = new Usuario();
        $usr->usuario = $usuario;
        $usr->clave = $clave;
        $usr->rol = $rol;

        if(Usuario::validarRol($usr->rol)){
          $usr->crearUsuario();
          $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
          $status = 200;
        }
        else{
          $payload = json_encode(array("mensaje" => "Error: rol invalido"));
          $status = 400;
        }
        
        $response = new Response();
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus($status);
    }
}
