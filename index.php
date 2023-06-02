<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use App\Zaptank\Account\Account;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

$app->addErrorMiddleware(false, true, true);

$app->setBasePath('/zaptank_api');

$app->get('/', function(Request $request, Response $response, array $args) {
    $response->getBody()->write('Hello, world!');
    return $response;
});

$app->post('/account/new', function(Request $request, Response $response, array $args) {

    $email = $_POST['email'];
    $password = strtoupper(md5($_POST['password']));
    $phone = $_POST['phone'];
    $ReferenceLocation = $_POST['ReferenceLocation'];

    $account = new Account;

    $account->register($email, $password, $phone, $ReferenceLocation);

    $response->getBody()->write(json_encode(true));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();