<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Stream;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Zaptank\Middlewares\RequestResponseLoggerMiddleware;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$logger = new Logger('slim');
$logger->pushHandler(new StreamHandler('logs/app.log', Logger::INFO));

$app = AppFactory::create();

$app->add(new RequestResponseLoggerMiddleware($logger));

// $app->addErrorMiddleware(false, true, true);

$app->setBasePath('/api');

$app->add(function ($request, $handler) use ($app) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

require __DIR__ . '/routes/Api.php';

$app->run();