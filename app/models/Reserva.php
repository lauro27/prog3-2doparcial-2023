<?php
require_once './dto/BuscaTotalesDTO.php';
class Reserva
{
    public $id;
    public $idCliente;
    public $fechaEntrada;
    public $fechaSalida;
    public $tipoHabitacion;
    public $importe;

    public function crearReserva()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO reservas (id_cliente, fecha_entrada, fecha_salida, tipo_habitacion, importe) VALUES (:idCliente, :fecha_entrada, :fecha_salida, :tipoHabitacion, :importe)");
        $consulta->bindValue(':id_cliente', $this->idCliente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_entrada', $this->fechaEntrada, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_salida', $this->fechaSalida, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_habitacion', $this->tipoHabitacion, PDO::PARAM_STR);
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_cliente, fecha_entrada, fecha_salida, tipo_habitacion, importe FROM reservas WHERE fecha_baja = null");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }

    #region CONSULTA RESERVAS
    public static function obtenerTodosTotal($tipo, $fecha = null)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        if ($fecha == null){
            $fecha = new DateTime(date("d-m-Y"));
            $fecha = date_modify($fecha, '-1 day');
        }
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(importe)FROM reservas where fecha_entrada = :fecha and tipo_habitacion = :tipo and  fecha_baja = null");
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $fecha);
        $consulta->execute();

        return $consulta->fetch();
    }
    public static function obtenerTodosCliente($idCliente)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_cliente, fecha_entrada, fecha_salida, tipo_habitacion, importe FROM reservas WHERE  fecha_baja = null");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }
    public static function obtenerTodosEntreFechas($fechaInicio, $fechaFinal)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM reservas WHERE fecha_baja = null and fecha_entrada BETWEEN :fecha_inicio and :fecha_final");
        $consulta->bindValue(':fecha_inicio', $fechaInicio);
        $consulta->bindValue(':fecha_final', $fechaFinal);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }
    public static function obtenerTodosTipo($tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM reservas WHERE fecha_baja = null and tipo_habitacion = :tipo");
        $consulta->bindValue(':tipo', $tipo);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }
    public static function obtenerCanceladosCliente($idCliente){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM reservas WHERE not fecha_baja = null and id_cliente = :id");
        $consulta->bindValue(':id', $idCliente, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }
    public static function obtenerCanceladosEntreFechas($fechaInicio, $fechaFinal)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM reservas WHERE not fecha_baja = null and fecha_entrada BETWEEN :fecha_inicio and :fecha_final order by fecha_entrada");
        $consulta->bindValue(':fecha_inicio', $fechaInicio);
        $consulta->bindValue(':fecha_final', $fechaFinal);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }

    #endregion


    public static function obtenerReserva($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_cliente, fecha_entrada, fecha_salida, tipo_habitacion, importe FROM reservas WHERE id = :id and  fecha_baja = null");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Reserva');
    }
    
    public function modificarReserva()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE reservas SET importe = :importe WHERE id = :id");
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    //
    public static function cancelarReserva($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE reservas SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}