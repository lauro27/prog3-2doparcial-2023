<?php
class ObtenerClienteDTO{
    public $pais;
    public $ciudad;
    public $telefono;

    public function __construct(Cliente $cliente)
    {
        $this->pais = $cliente->pais;
        $this->ciudad = $cliente->ciudad;
        $this->telefono = $cliente->telefono;
    }
}