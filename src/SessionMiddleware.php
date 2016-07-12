<?php

namespace Session;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SessionMiddleware
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);

        if ($this->session->started()) {
            $this->session->close();

            return $response->withAddedHeader('Set-Cookie', $this->session->cookie());
        }

        return $response;
    }
}
