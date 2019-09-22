<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//controllers
use App\Controllers\SolicitacaoController;

$app->get('/', function (Request $request, Response $response, array $args) {
 
    //$response->getBody()->write("Hello");
    // return $response;
      //return $this->get('view')->render($response, 'home.html');
  
      return SolicitacaoController::getAll($request, $response,$args);
   
  });
  
  $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
      $name = $args['name'];
      $response->getBody()->write("Hello, $name");
      return $response;
  });
  

