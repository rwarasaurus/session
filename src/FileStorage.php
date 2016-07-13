<?php

namespace Session;

class FileStorage implements StorageInterface
{
    protected $path;

    public function __construct(string $path)
    {
        $this->path = realpath($path);
    }

    public function read(string $id): array
    {
        $path = sprintf('%s/%s.sess', $this->path, $id);

        $contents = file_get_contents($path);

        return json_decode($contents, true);
    }

    public function exists(string $id): bool
    {
        $path = sprintf('%s/%s.sess', $this->path, $id);

        return is_file($path);
    }

    public function write(string $id, array $data): bool
    {
        $path = sprintf('%s/%s.sess', $this->path, $id);

        $jsonString = json_encode($data);

        if (false === file_put_contents($path, $jsonString, LOCK_EX)) {
            throw new \RuntimeException('Failed to write session file');
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $path = sprintf('%s/%s.sess', $this->path, $id);

        return is_file($path) && unlink($path);
    }
}
