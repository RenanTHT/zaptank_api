<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Controllers\Account\AccountController;
use App\Zaptank\Controllers\Account\ConfigController;

use App\Zaptank\Middlewares\Auth\ensureJwtAuthTokenIsValid;

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

$app->post('/account/phone/change', [ConfigController::class, 'changePhone'])->add(new ensureJwtAuthTokenIsValid);
$app->post('/account/password/change', [ConfigController::class, 'changePassword'])->add(new ensureJwtAuthTokenIsValid);
$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);

$app->run();