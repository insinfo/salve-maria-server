<?php

namespace App\Models;

class Denuncia{
    const TABLE_NAME ="denuncias";
    protected $id;
    private $formaViolencia;//física, psicológica, patrimonial, moral, sexual
    private $nesteMomento;//bool
    private $demandante;//anônimo, própria vítima, famaliar, vizinho
    private $nomeVitima;//varchar
    private $idadeVitima;//int4
    private $cor;//amarela,branca,indígena,negra, parda
    private $nomeAgressor;
    private $logradouroOcorrencia;
    private $numero;
    private $complemento;
    private $cep;
    private $bairro;
    private $reincidencia;//bool
    private $foto;//
    private $video;//
    private $audio;//
    private $observacao;
    private $dataCadastro;//timestamp
}