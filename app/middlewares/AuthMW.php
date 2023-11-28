<?php

use GuzzleHttp\Psr7\Response;

class AuthMW
{
    public static function ValidarToken($request, $handler){
        $token = $request->getHeader("token");
        $validacionToken = AutentificadorJWT::ObtenerPayload($token[0]);
        if($validacionToken["Estado"] == "OK"){
            $request = $request->withAttribute("payload", $validacionToken);
            $response = $handler->handle($request);
            return $response;
        }
        else{
            $response = new Response();
            $response = $response->withStatus(403, 'Error de Token');
            return $response;
        }
    }

    public static function LoginGerente($request, $handler){
        $header = $request->getHeaderLine("authorization");
        
        $response = new Response();
        
        try{
            $token = trim(explode('Bearer', $header)[1]);
            $payload = AutentificadorJWT::ObtenerData($token);
            if($payload->rol != "Gerente"){ throw new Exception("No autorizado");}
        }
        catch(Exception $e){
            $payload = json_encode(array('error'=> $e->getMessage()));
            $response = $response->withStatus(403, $e->getMessage());
            return $response;
        }
        $response = $handler->handle($request);
        return $response;
    }

    
    public static function LoginRecepcionistaCliente($request, $handler){
        $header = $request->getHeaderLine("authorization");
        
        $response = new Response();
        
        try{
            $token = trim(explode('Bearer', $header)[1]);
            $payload = AutentificadorJWT::ObtenerData($token);
            if($payload->rol != "Recepcionista" && $payload->rol != "Cliente"){ throw new Exception("No autorizado");}
        }
        catch(Exception $e){
            $payload = json_encode(array('error'=> $e->getMessage()));
            $response = $response->withStatus(403, $e->getMessage());
            return $response;
        }
        $response = $handler->handle($request);
        return $response;
    }
}