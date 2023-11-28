<?php
use Slim\Psr7\Response;

require_once './models/Reserva.php';
require_once './interfaces/IApiUsable.php';
require_once './dto/BuscaTotalesDTO.php';

class ReservaController extends Reserva implements IApiUsable
{
    //PUNTO 3 A
    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $tipoCliente = $parametros['tipo_cliente'];
      $numero = $parametros['numero'];
      $cliente = Cliente::obtenerCliente($tipoCliente, $numero);
      
      $idCliente = $parametros['id_cliente'];
      $fechaEntrada = $parametros['fecha_entrada'];
      $fechaSalida = $parametros['fecha_salida'];
      $tipoHabitacion = $parametros['tipo_habitacion'];
      
      
      $usr = new Reserva();
      $usr->idCliente = $idCliente;
      $usr->fechaEntrada = $fechaEntrada;
      $usr->fechaSalida = $fechaSalida;
      $usr->tipoHabitacion = $tipoHabitacion;
      
      if (!isset($cliente->documento)) {
        $payload = json_encode(array("mensaje" => "No se encuentra cliente valido"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
      }

      $id = $usr->crearReserva();

      //PUNTO 3 B
      $files = $request->getUploadedFiles();
      var_dump($files);
      if(isset($files['foto']))
      {
          if(!file_exists('ImagenesDeReservas2023/')){
              mkdir('ImagenesDeReservas2023/',0777, true);
          }
          $foto = $files['foto'];
          $media = $foto->getClientMediaType();
          $ext = explode("/", $media)[1];
          $type = explode("/", $media)[0];
          if($type == "image")
          {
            $tipos = $cliente->separarTipo();
            $ruta = "./Mesas/" . $cliente->numero . substr($tipos[0],0,1) . substr($tipos[1],0,1) . $id ."." . $ext;
            $foto->moveTo($ruta);
          }
          else{$ruta = "";}
      }
      else{$ruta = "";}
      
    
      $payload = json_encode(array("mensaje" => "Reserva creada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

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
    /************************/
    /*TERMINAR PUNTOS 4 Y 10*/
    /************************/
    public function TraerTodos($request, $response, $args)
    {
      $consulta = $args["consulta"];

      $parametros = $request->getQueryParams();
      switch ($consulta) {
        case 'total':
          //4-A
          $fecha = isset($parametros["fecha"])? $parametros["fecha"] : null;
          if (isset($parametros["tipo"]))
          {
            $payload = Reserva::obtenerTodosTotal($parametros["tipo"], $fecha);
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'cliente':
          //4-B
          if (isset($parametros["tipo_cliente"]) && isset($parametros["numero"]))
          {
            $tipoCliente = $parametros["tipo_cliente"];
            $numero = $parametros["numero"];
            $cliente = Cliente::obtenerCliente($tipoCliente, $numero);
            if(isset($cliente->documento))
              {$payload = Reserva::obtenerTodosCliente($cliente->id);}
            else
              {$payload = array("error" => "No se encuentra el cliente");} 
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'fechas':
          //4-C
          if (isset($parametros["fecha_inicio"]) && isset($parametros["fecha_final"]))
          {
            $payload = Reserva::obtenerTodosEntreFechas($parametros["fecha_inicio"], $parametros["fecha_final"]);
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'habitacion':
          //4-D
          if (isset($parametros["tipo"]))
          {
            $tipo = $parametros["tipo"];
            $payload = Reserva::obtenerTodosTipo($tipo);
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'cancelados_tipo_fecha':
          break;
        case 'cancelados_cliente':
          //10-B
          if (isset($parametros["tipo_cliente"]) && isset($parametros["numero"]))
          {
            $tipoCliente = $parametros["tipo_cliente"];
            $numero = $parametros["numero"];
            $cliente = Cliente::obtenerCliente($tipoCliente, $numero);
            if(isset($cliente->documento))
              {$payload = Reserva::obtenerCanceladosCliente($cliente->id);}
            else
              {$payload = array("error" => "No se encuentra el cliente");} 
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'cancelados_fecha':
          //10-C
          if (isset($parametros["fecha_inicio"]) && isset($parametros["fecha_final"]))
          {
            $payload = Reserva::obtenerCanceladosEntreFechas($parametros["fecha_inicio"], $parametros["fecha_final"]);
          }
          else{
            $payload = array("error" => "Faltan parametros");
          }
          break;
        case 'cancelados_tipo':
          
          break;
        case 'operaciones':
          break;
        case 'modalidad':
          break;

        default:
          $payload = json_encode(array("mensaje" => "Opcion invalida"));
          break;
      }
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    
    //PUNTO 7
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idReserva = $parametros['id_reserva'];
        $importe = intval($parametros['importe']);
        $causa = $parametros['causa'];

        $reserva = Reserva::obtenerReserva($idReserva);

        if(!isset($reserva->importe)){
          $payload = json_encode(array("mensaje" => "No se encuentra reserva"));
        }
        else{
          $reserva->importe = $importe;
          $reserva->modificarReserva();
          $ajuste = new Ajuste();
          $ajuste->idReserva = $idReserva;
          $ajuste->importe = $importe;
          $ajuste->causa = $causa;
          $ajuste->crearAjuste();
          $payload = json_encode(array("mensaje" => "Ajuste realizado"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    //PUNTO 6
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipoCliente = $parametros["tipo_cliente"];
        $numero = $parametros["numero"];
        $idReserva = $parametros["id_reserva"];

        $reserva = Reserva::obtenerReserva($idReserva);
        $cliente = Cliente::obtenerCliente($tipoCliente, $numero);
        
        if(!isset($cliente->numero)){
          $payload = array("mensaje" => "Cliente no valido");
        }
        elseif(!isset($reserva->tipoHabitacion)){
          $payload = array("mensaje" => "Reserva no valida");
        }
        else{
          Reserva::cancelarReserva($reserva->id);
          $payload = array("mensaje" => "Reserva cancelada con exito");
        }

        $response->getBody()->write(json_encode($payload));
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
