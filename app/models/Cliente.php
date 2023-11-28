<?php

class Cliente
{
    public $id;
    public $nombre;
    public $numero;
    public $documento;
    public $modoPago = "efectivo";
    public $tipoCliente;
    public $pais;
    public $ciudad;
    public $telefono;
    public $fechaBaja;

    public function crearCliente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO clientes (nombre, numero, documento, modo_pago, tipo_cliente, pais, ciudad, telefono) VALUES (:nombre, :numero, :dni, :modo_pago, :tipo_cliente, :pais, :ciudad, :telefono)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':numero', $this->numero, PDO::PARAM_STR);
        $consulta->bindValue(':documento', $this->documento, PDO::PARAM_STR);
        $consulta->bindValue(':modo_pago', $this->modoPago, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_cliente', $this->tipoCliente, PDO::PARAM_STR);
        $consulta->bindValue(':pais', $this->pais, PDO::PARAM_STR);
        $consulta->bindValue(':ciudad', $this->ciudad, PDO::PARAM_STR);
        $consulta->bindValue(':telefono', $this->telefono, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, numero, documento, modo_pago, tipo_cliente, pais, ciudad, telefono FROM clientes where fecha_baja = null");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');
    }
    
    public static function obtenerPorNro($numero)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, numero, documento, modo_pago, tipo_cliente, pais, ciudad, telefono FROM clientes where numero = :numero and fecha_baja = null");
        $consulta->bindValue(':numero', $numero, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');

    }

    public static function obtenerCliente($tipo, $numero)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, numero, documento, modo_pago, tipo_cliente, pais, ciudad, telefono FROM clientes WHERE tipo_cliente = :tipo_cliente and numero = :numero and  fecha_baja = null");
        $consulta->bindValue(':tipo_cliente', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':numero', $numero, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cliente');
    }


    public function modificarCliente()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes SET 
                nombre = :nombre, 
                numero = :numero, 
                documento = :documento,
                modo_pago = :modo_pago, 
                tipo_cliente = :tipo_cliente, 
                pais = :pais, 
                ciudad = :ciudad, 
                telefono = :telefono
                WHERE id = :id and  fecha_baja = null");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':numero', $this->numero, PDO::PARAM_STR);
        $consulta->bindValue(':documento', $this->documento, PDO::PARAM_STR);
        $consulta->bindValue(':modo_pago', $this->modoPago, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_cliente', $this->tipoCliente, PDO::PARAM_STR);
        $consulta->bindValue(':pais', $this->pais, PDO::PARAM_STR);
        $consulta->bindValue(':ciudad', $this->ciudad, PDO::PARAM_STR);
        $consulta->bindValue(':telefono', $this->telefono, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarCliente($tipoCliente, $numero)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes SET fecha_baja = :fecha_baja WHERE numero = :numero and tipo_cliente = :tipo_cliente");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':numero', $numero, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_cliente', $tipoCliente, PDO::PARAM_STR);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    public function separarTipo()
    {
        $result = explode($this->tipoCliente, '-');
        return $result;
    }

    //Devuelve c si el tipo de cliente es invalido y d si el tipo de documento
    public static function validarTipo($tipo){
        $tipos =explode($tipo, '-');
        $errors = "";
        if ($tipos[0] != "INDI" && 
                $tipos[0] != "CORPO"){
            $errors .= "c";
        }
        if($tipos[1] != "DNI" && 
                $tipos[1] != "LE" &&
                $tipos[1] != "LC" &&
                $tipos[1] != "PASAPORTE"){
            $errors .= "d";
        }

        return $errors;
    }

    public static function generarCodigo()
    {
        return substr(str_shuffle(str_repeat("0123456789", 5)), 0, 5);
    }
}