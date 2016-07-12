<?php

namespace Session;

class Cookies implements CookiesInterface
{
    protected $cookies;

    public function __construct(array $cookies = null)
    {
        $this->cookies = null === $cookies ? $_COOKIE : $cookies;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->cookies);
    }

    public function get(string $name, $default = null)
    {
        return $this->has($name) ? $this->cookies[$name] : $default;
    }
}
