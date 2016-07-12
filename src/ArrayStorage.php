<?php

namespace Session;

class ArrayStorage implements StorageInterface
{
    protected $array = [];

    public function read(string $id): array
    {
        if (false === isset($this->array[$id])) {
            return [];
        }

        return json_decode($this->array[$id], true);
    }

    public function write(string $id, array $data): bool
    {
        $this->array[$id] = json_encode($data);

        return true;
    }

    public function destroy(string $id): bool
    {
        unset($this->array[$id]);

        return isset($this->array[$id]);
    }
}
