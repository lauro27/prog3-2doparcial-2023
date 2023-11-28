<?php
require_once './models/Usuario.php';
require_once './util/AuthJWT.php';

use Slim\Psr7\Response;

class LoginController{

    public function IniciarSesion($request, $handler){
        $arrayParam = $request->getParsedBody();
        $user = $arrayParam['usuario'];
        $pass = $arrayParam['clave'];

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $cmp = Usuario::obtenerUsuario($user);

        if(password_verify($pass, $cmp->clave)){
            $value = $cmp;
        }

        $response = new Response();
        if(!isset($value->usuario) || !is_null($value->fecha_baja)){
            $payload = json_encode(array("mensaje" => "Usuario no existente"));
            $response->getBody()->write($payload);
            return $response->withStatus(401, 'Unauthorized');
        }
        else{
            $datos = array("usuario" => $value->usuario, "id" => $value->id, "rol" => $value->rol);
            $token = AutentificadorJWT::CrearToken($datos);
            $rol = $value->rol;
            $response->getBody()->write(json_encode(array("token" => $token)));
            return $response->withStatus(200, 'OK ' . $rol);
        }
    }
}

?>