<?php declare(strict_types=1);

namespace Session;

class Cookies implements CookiesInterface
{
    protected $cookies;

    public function __construct(array $cookies = [])
    {
        $this->cookies = $cookies;
    }

    public static function fromGlobals()
    {
        return new self($_COOKIE);
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
