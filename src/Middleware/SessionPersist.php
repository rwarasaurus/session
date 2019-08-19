<?php declare(strict_types=1);

namespace Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionPersist implements MiddlewareInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->session->started()) {
            $this->session->rotate()->close();
            $response = $response->withHeader('Set-Cookie', $this->session->cookie());
        }

        return $response;
    }
}
