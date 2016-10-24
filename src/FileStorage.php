<?php

namespace Session;

class FileStorage implements StorageInterface
{
    protected $path;

    protected $expire;

    public function __construct(string $path, int $expire = 3600)
    {
        $this->path = realpath($path);
        $this->expire = $expire;
    }

    public function purge()
    {
        $handle = opendir($this->path);
        $now = time();

        while (false !== ($filepath = readdir($handle))) {
            // last modification time
            $mtime = filemtime($filepath);

            // file expired
            if (($mtime + $this->expire) < $now) {
                unlink($filepath);
            }
        }
    }

    protected function filepath(string $id): string
    {
        return sprintf('%s/%s.sess', $this->path, $id);
    }

    public function read(string $id): array
    {
        $path = $this->filepath($id);

        $contents = file_get_contents($path);

        return json_decode($contents, true);
    }

    public function exists(string $id): bool
    {
        $path = $this->filepath($id);

        return is_file($path);
    }

    public function write(string $id, array $data): bool
    {
        $path = $this->filepath($id);

        $jsonString = json_encode($data);

        if (false === file_put_contents($path, $jsonString, LOCK_EX)) {
            throw new \RuntimeException('Failed to write session file');
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $path = $this->filepath($id);

        return is_file($path) && unlink($path);
    }
}
