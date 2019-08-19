<?php declare(strict_types=1);

namespace Session\Storage;

interface StorageInterface
{
    public function read(string $id): array;

    public function exists(string $id): bool;

    public function write(string $id, array $data): bool;

    public function destroy(string $id): bool;
}
