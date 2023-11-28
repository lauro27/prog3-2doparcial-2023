<?php
use Slim\Psr7\Response;

require_once './models/Cliente.php';
require_once './interfaces/IApiUsable.php';

class ClienteController extends Cliente implements IApiUsable
{
    //PUNTO 1B
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $documento = $parametros['documento'];
        $modoPago = isset($parametros['modo_pago'])? $parametros['modo_pago']: "efectivo";
        $tipoCliente = $parametros['tipo_cliente'];
        $pais = $parametros['pais'];
        $ciudad = $parametros['ciudad'];
        $telefono = $parametros['telefono'];

        $numero = Cliente::GenerarCodigo();

        $cliente = Cliente::obtenerCliente($numero, $tipoCliente);

        $usr = new Cliente();
        $usr->nombre = $nombre;
        $usr->numero = $numero;
        $usr->documento = $documento;
        $usr->modoPago = $modoPago;
        $usr->tipoCliente = $tipoCliente;
        $usr->pais = $pais;
        $usr->ciudad = $ciudad;
        $usr->telefono = $telefono;

        //FOTO ACA
        $files = $request->getUploadedFiles();
        //var_dump($files);
        if(isset($files['foto']))
        {
            if(!file_exists('ImagenesDeClientes/2023/')){
                mkdir('ImagenesDeClientes/2023/',0777, true);
            }
            $foto = $files['foto'];
            $media = $foto->getClientMediaType();
            $ext = explode("/", $media)[1];
            $type = explode("/", $media)[0];
            if($type == "image")
            {
              $tipos = $usr->separarTipo();
              $ruta = "./ImagenesDeClientes/2023/" . $usr->numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . "." . $ext;
              $foto->moveTo($ruta);
            }
            else{$ruta = "";}
        }
        else{$ruta = "";}


        $response = new Response();
        if (isset($cliente->id)){
          //YA EXISTE a modificar el que existe
          $usr->id = $cliente->id;
          $usr->modificarCliente();
          $payload = json_encode(array("mensaje" => "Cliente modificado con exito"));
        }
        else{
          // Creamos el cliente
          $usr->crearCliente();
          $payload = json_encode(array("mensaje" => "Cliente creado con exito"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    //PUNTO 2
    public function TraerUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipoCliente = $parametros['tipo_cliente'];
        $numero = $parametros['numero'];
        $clientes = Cliente::obtenerPorNro($numero);
        $cliente = null;
        if(count($clientes) > 0){
          foreach ($clientes as $key => $value) {
            if ($value->tipoCliente == $tipoCliente){
              $cliente = $value;
              break;
            }
          }
          if ($cliente != null) {
            $payload = json_encode(new ObtenerClienteDTO($cliente));
          }
          else{
            $payload = json_encode(array("error" => "Tipo de cliente incorrecto"));  
          }
        }
        else{
          $payload = json_encode(array("error" => "Tipo de cliente incorrecto"));
        }

        

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Cliente::obtenerTodos();
        $payload = json_encode(array("listaCliente" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    //PUNTO 5
    public function ModificarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $nombre = $parametros['nombre'];
      $numero = $parametros['numero'];
      $documento = $parametros['documento'];
      $modoPago = $parametros['modo_pago'];
      $tipoCliente = $parametros['tipo_cliente'];
      $pais = $parametros['pais'];
      $ciudad = $parametros['ciudad'];
      $telefono = $parametros['telefono'];
      
      $cliente = Cliente::obtenerCliente($numero, $tipoCliente);

      $usr = new Cliente();
      $usr->id = $cliente;
      $usr->nombre = $nombre;
      $usr->numero = $numero;
      $usr->documento = $documento;
      $usr->modoPago = $modoPago;
      $usr->tipoCliente = $tipoCliente;
      $usr->pais = $pais;
      $usr->ciudad = $ciudad;
      $usr->telefono = $telefono;
      Cliente::modificarCliente($nombre);
      
      
      $response = new Response();
      if (isset($cliente->id)){
        //YA EXISTE a modificar el que existe
        $usr->id = $cliente->id;
        $usr->modificarCliente();  
        $payload = json_encode(array("mensaje" => "Cliente modificado con exito"));
      }
      else{
        $payload = json_encode(array("mensaje" => "No se encuentra cliente"));
      }
      
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    //PENDIENTE PUNTO 9
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipoCliente = $parametros['tipo_cliente'];
        $numero = $parametros['numero'];

        $cliente = Cliente::obtenerCliente($tipoCliente, $numero);

        if (!isset($cliente->id)){
          $payload = json_encode(array("mensaje" => "Cliente no existe"));
        }
        else{
          Cliente::borrarCliente($tipoCliente, $numero);

          $payload = json_encode(array("mensaje" => "Cliente borrado con exito"));
          $tipos = $cliente->separarTipo();
          $ruta = "./ImagenesDeClientes/2023/" . $numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . ".*";
          $filenames = glob($ruta);
          if(count($filenames) > 0){
            $onlyfile = $filenames[0];
            $ext = explode(".", $onlyfile)[2];
            rename($onlyfile, "./ImagenesBackupClientes/2023/" . $numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . "." . $ext);
          }
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
