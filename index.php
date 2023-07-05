<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

use App\Zaptank\Controllers\AccountController;
use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Services\Token;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

// $app->addErrorMiddleware(false, true, true);

$app->setBasePath('/zaptank_api');

$app->add(function ($request, $handler) use ($app) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$middleware = function(Request $request, RequestHandler $handler) {
    
    $response = $handler->handle($request);

    $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
    $existingContent = (string) $response->getBody();

    $token = new Token;
    $decode = $token->validate($jwt);

    $response = new Slim\Psr7\Response();
    $response->getBody()->write(json_encode([
        [
            'Middleware' => [
                'token' =>  $jwt,
                'decode' => $decode
            ],
            'Route' => $existingContent
        ]
    ]));

    return $response;
};

$app->post('/account/phone/change', [AccountController::class, 'changeEmail'])->add($middleware);

$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);

$app->run();