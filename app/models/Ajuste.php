<?php

class Ajuste{
    public $id;
    public $idReserva;
    public $importe;
    public $causa;

    public function crearAjuste()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (id_reserva, importe, causa) VALUES (:id_reserva, :importe, :causa)");
        $consulta->bindValue(':id_reserva', $this->idReserva, PDO::PARAM_STR);
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
        $consulta->bindValue(':causa', $this->causa, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}