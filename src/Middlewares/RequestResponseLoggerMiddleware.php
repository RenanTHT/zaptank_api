<?php

namespace App\Zaptank\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\ResponseFactory;

class RequestResponseLoggerMiddleware
{
    protected $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, RequestHandler $handler) {

        $this->logger->info('Request:', [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string)$request->getBody(),
        ]);

        $response = $handler->handle($request);

        $this->logger->info('Response:', [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody(),
        ]);

        return $response;
    }
}
