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

$app->add(function ($request, $handler) use ($app) {

    $allowedOrigins = ['http://localhost', 'https://appws.picpay.com', 'https://api.pagar.me'];

    $response = $handler->handle($request);
    $origin = $request->getHeaderLine('Origin');

    if (in_array($origin, $allowedOrigins)) {
        return $response = $response
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }

    $body = json_encode([
        'error' => true,
        'message' => 'O servidor não consegue responder a sua solicitação',
        'origin' => $origin
    ]);

    $response = new Response;
    $response->getBody()->write($body);
    return $response->withStatus(403);
});

require __DIR__ . '/routes/Api.php';

$app->run();