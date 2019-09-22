<?php

$BASE_DIR = dirname(__FILE__);
$VIEWS_DIR = $BASE_DIR.'/views';
$CACHE_DIR = $BASE_DIR.'/cache';

//require '../vendor/autoload.php';
require __DIR__ . '/../vendor/autoload.php';


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;

use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
//use Slim\Views\TwigMiddleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;
class TwigMiddleware implements MiddlewareInterface
{
    /**
     * @var Twig
     */
    protected $twig;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var RouteParserInterface
     */
    protected $routeParser;
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @param Twig                 $twig
     * @param ContainerInterface   $container
     * @param RouteParserInterface $routeParser
     * @param string               $basePath
     */
    public function __construct(Twig $twig, ContainerInterface $container, RouteParserInterface $routeParser, string $basePath = '')
    {
        $this->twig = $twig;
        $this->container = $container;
        $this->routeParser = $routeParser;
        $this->basePath = $basePath;
    }
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
      //$basePath = rtrim(str_ireplace('index.php', '', $request->getUri()->getBasePath()), '/');

        $extension = new TwigExtension($this->routeParser, $request->getUri(), $this->basePath);
        $this->twig->addExtension($extension);
        if (method_exists($this->container, 'set')) {
            $this->container->set('view', $this->twig);
        } elseif ($this->container instanceof ArrayAccess) {
            $this->container['view'] = $this->twig;
        }
        return $handler->handle($request);
    }
}

// Create Container
$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::create();

// This middleware will append the response header Access-Control-Allow-Methods with all allowed methods
$app->add(function($request, $handler) {
  $routeContext = RouteContext::fromRequest($request);
  $routingResults = $routeContext->getRoutingResults();
  //$methods = $routingResults->getAllowedMethods();
  $methods = ['total-records','GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
  
  $response = $handler->handle($request);
  $response = $response->withHeader('Access-Control-Allow-Methods', implode(",", $methods));
  
  return $response;
});

// Add Routing Middleware
$app->addRoutingMiddleware();

/*
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Add Twig-View Middleware

$routeParser = $app->getRouteCollector()->getRouteParser();
$twig = new Twig($VIEWS_DIR, ['cache' => false]/*['cache' => $CACHE_DIR]*/);
$twigMiddleware = new TwigMiddleware($twig, $app->getContainer(), $routeParser);
$app->add($twigMiddleware);

//CORS - Cross origin resource sharing
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
          //->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

require __DIR__ .'/../src/routes.php';

use Slim\Exception\HttpNotFoundException;

/*
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();


function mypriny(string $input,$tag=null){
  $re= " $input </br>";
  if($tag != null){
    $re = "$tag ".$re;
  }
    echo $re;
}
