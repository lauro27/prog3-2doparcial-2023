<?php

class Ajuste{
    public $id;
    public $id_reserva;
    public $importe_nuevo;
    public $causa;

    public function crearAjuste()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (id_reserva, importe_nuevo, causa) VALUES (:id_reserva, :importe, :causa)");
        $consulta->bindValue(':id_reserva', $this->id_reserva, PDO::PARAM_STR);
        $consulta->bindValue(':importe', $this->importe_nuevo, PDO::PARAM_INT);
        $consulta->bindValue(':causa', $this->causa, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}