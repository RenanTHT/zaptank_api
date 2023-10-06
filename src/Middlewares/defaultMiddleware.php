<?php

namespace App\Zaptank\Middlewares\Gift;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class checkIfTheRewardCodeIsValid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        echo 'ok';
        return $handler->handle($request);
    }
}