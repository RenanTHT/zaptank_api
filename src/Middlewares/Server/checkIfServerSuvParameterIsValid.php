<?php

namespace App\Zaptank\Middlewares\Server;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class checkIfServerSuvParameterIsValid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        echo 'checkIfServerSuvParameterIsValid';
        return $handler->handle($request);
    }
}