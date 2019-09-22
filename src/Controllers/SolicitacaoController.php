<?php //declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use  App\Util\DBLayer;


class  SolicitacaoController{   

    public static function getAll(Request $request, Response $response, array $args){
        DBLayer::Connect();
        $data = DBLayer::table('solicitacoes')->get();


        //$data = array('name' => 'Bob', 'age' => 40);
        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
                ->withHeader('Content-Type', 'application/json');
    }
}