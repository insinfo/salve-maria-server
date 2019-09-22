<?php

namespace App\Models;

class Alerta{
    const TABLE_NAME ="alertas";    
    protected $id;
    private $latitude;
    private $longitude;
    private $cpf;
    private $logradouro;
    private $numero;
    private $bairro;
    private $cidade;
    private $estado;
    private $imei;
    private $complemento;
    private $observacao;
    private $dataCadastro;//timestamp
}