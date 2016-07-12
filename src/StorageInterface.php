<?php

namespace Session;

interface StorageInterface
{
    public function read(string $id): array;

    public function write(string $id, array $data): bool;

    public function destroy(string $id): bool;
}
