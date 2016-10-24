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
        clearstatcache();
        $handle = opendir($this->path);
        $now = time();

        try {
            while (false !== ($filepath = readdir($handle))) {
                // file expired
                if ($this->expired($filepath, $now)) {
                    unlink($filepath);
                }
            }
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('failed to unlink file: %s', $filepath), 0, $exception);
        } finally {
            closedir($handle);
        }
    }

    protected function expired(string $filepath, int $time): bool
    {
        if (! is_file($filepath)) {
            return true;
        }

        // last modification time
        $mtime = filemtime($filepath);

        return ($mtime + $this->expire) < $time;
    }

    protected function filepath(string $id): string
    {
        return sprintf('%s/%s.sess', $this->path, $id);
    }

    public function read(string $id): array
    {
        $filepath = $this->filepath($id);
        $now = time();

        if ($this->expired($filepath, $now)) {
            return [];
        }

        $contents = file_get_contents($path);

        return json_decode($contents, true);
    }

    public function exists(string $id): bool
    {
        $filepath = $this->filepath($id);
        $now = time();

        return ! $this->expired($filepath, $now);
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
