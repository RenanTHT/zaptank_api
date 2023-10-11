<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

// $app->addErrorMiddleware(false, true, true);

$app->setBasePath('/api');

$app->add(function ($request, $handler) use ($app) {

    $allowedOrigins = ['http://localhost', 'https://appws.picpay.com', 'https://api.pagar.me'];

    $response = $handler->handle($request);
    $origin = $request->getHeaderLine('Origin');

    if (in_array($origin, $allowedOrigins)) {
        return $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }

    $body = json_encode([
        'error' => true,
        'message' => 'O servidor não consegue responder a sua solicitação'
    ]);

    $response = new Response;
    $response->getBody()->write($body);
    return $response->withStatus(403);
});

require __DIR__ . '/routes/Api.php';

$app->run();