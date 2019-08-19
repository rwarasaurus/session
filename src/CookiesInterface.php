<?php declare(strict_types=1);

namespace Session;

interface CookiesInterface
{
    /***/
    public function has(string $name): bool;

    public function get(string $name, $default = null);
}
