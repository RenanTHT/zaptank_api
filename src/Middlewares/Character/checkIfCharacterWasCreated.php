<?php

namespace App\Zaptank\Middlewares\Character;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class checkIfCharacterWasCreated {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        echo 'checkIfCharacterWasCreated';
        return $handler->handle($request);
    }
}