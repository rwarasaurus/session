<?php declare(strict_types=1);

namespace Session\Storage;

class ArrayStorage implements StorageInterface
{
    use Encoding;

    protected $array = [];

    public function read(string $id): array
    {
        return $this->decode($this->array[$id]);
    }

    public function exists(string $id): bool
    {
        return isset($this->array[$id]);
    }

    public function write(string $id, array $data): bool
    {
        $this->array[$id] = $this->encode($data);

        return true;
    }

    public function destroy(string $id): bool
    {
        unset($this->array[$id]);

        return isset($this->array[$id]);
    }
}
