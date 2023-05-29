<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath('/zaptank');

$app->get('/', function(Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->post('/fruits', function(Request $request, Response $response, array $args) {
    $fruit = [
        'nome' => 'Laranja',
        'cor'  => 'Laranja'
    ];
    $response->getBody()->write(json_encode($fruit));
    return $response->withHeader('Content-type', 'application/json');
});

$app->run();