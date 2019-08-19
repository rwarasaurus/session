<?php declare(strict_types=1);

namespace Session;

interface SessionInterface extends StashInterface
{
    public function id(): string;

    public function name(): string;

    public function migrate();

    public function destroy();

    public function start();

    public function started(): bool;

    public function close();

    public function has(string $key): bool;

    public function get(string $key);

    public function put(string $key, $value);

    public function remove(string $key);
}
