<?php
use Slim\Psr7\Response;

require_once './models/Cliente.php';
require_once './interfaces/IApiUsable.php';
require_once './dto/ObtenerClienteDTO.php';


class ClienteController extends Cliente implements IApiUsable
{
    //PUNTO 1-B
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $documento = $parametros['documento'];
        $modo_pago = isset($parametros['modo_pago'])? $parametros['modo_pago']: "efectivo";
        $tipo_cliente = $parametros['tipo_cliente'];
        $pais = $parametros['pais'];
        $ciudad = $parametros['ciudad'];
        $telefono = $parametros['telefono'];

        $numero = Cliente::GenerarCodigo();

        $cliente = Cliente::obtenerPorDoc($tipo_cliente, $documento);
        $usr = new Cliente();
        $usr->nombre = $nombre;
        $usr->numero = $numero;
        $usr->documento = $documento;
        $usr->modo_pago = $modo_pago;
        $usr->tipo_cliente = trim($tipo_cliente);
        $usr->pais = $pais;
        $usr->ciudad = $ciudad;
        $usr->telefono = $telefono;

        $response = new Response();
        if(Cliente::validarTipo($usr->tipo_cliente) == "")
        {
          if (isset($cliente->id)){
            //YA EXISTE a modificar el que existe
            $usr->id = $cliente->id;
            $usr->modificarCliente();
            $payload = json_encode(array("mensaje" => "Cliente modificado con exito"));
          }
          else{
            // Creamos el cliente
            $usr->crearCliente();
            //FOTO ACA
            $files = $request->getUploadedFiles();
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
            $payload = json_encode(array("mensaje" => "Cliente creado con exito"));
          }
        }
        else{
          $payload = json_encode(array("mensaje" => "Tipo de cliente no valido"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    //PUNTO 2
    public function TraerUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipo_cliente = $parametros['tipo_cliente'];
        $numero = $parametros['numero'];
        $clientes = Cliente::obtenerClientesPosibles($tipo_cliente,$numero);
        $cliente = null;
        if(count($clientes) > 0){
          foreach ($clientes as $key => $value) {
            if ($value->tipo_cliente == $tipo_cliente){
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
      $modo_pago = $parametros['modo_pago'];
      $tipo_cliente = $parametros['tipo_cliente'];
      $pais = $parametros['pais'];
      $ciudad = $parametros['ciudad'];
      $telefono = $parametros['telefono'];
      
      $cliente = Cliente::obtenerCliente($tipo_cliente, $numero);

      $usr = new Cliente();
      $usr->id = $cliente;
      $usr->nombre = $nombre;
      $usr->numero = $numero;
      $usr->documento = $documento;
      $usr->modo_pago = $modo_pago;
      $usr->tipo_cliente = $tipo_cliente;
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

    //PUNTO 9
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipo_cliente = $parametros['tipo_cliente'];
        $numero = $parametros['numero'];

        $cliente = Cliente::obtenerCliente($tipo_cliente, $numero);

        if (!isset($cliente->id)){
          $payload = json_encode(array("mensaje" => "Cliente no existe"));
        }
        else{
          Cliente::borrarCliente($tipo_cliente, $numero);

          $payload = json_encode(array("mensaje" => "Cliente borrado con exito"));
          $tipos = $cliente->separarTipo();

          if(!file_exists('ImagenesBackupClientes/2023/')){
            mkdir('ImagenesBackupClientes/2023/',0777, true);
        }

          $ruta = "./ImagenesDeClientes/2023/" . $numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . ".*";
          var_dump($ruta);
          $filenames = glob($ruta);
          var_dump($filenames);
          if(count($filenames) > 0){
            $onlyfile = $filenames[0];
            $ext = explode(".", $onlyfile)[2];
            var_dump($onlyfile);
            rename($onlyfile, "./ImagenesBackupClientes/2023/" . $numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . "." . $ext);
          }
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
