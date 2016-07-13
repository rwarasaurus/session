<?php

namespace Session;

use Redis;
use InvalidArgumentException;
use RuntimeException;

class RedisStorage implements StorageInterface
{
    protected $server;

    protected $expire;

    protected $prefix;

    public function __construct(Redis $server, int $expire = 3600, string $prefix = 'sess_')
    {
        if (is_numeric(substr($prefix, 0, 1))) {
            throw new InvalidArgumentException('prefix can not start with a number');
        }

        $this->server = $server;
        $this->expire = $expire;
        $this->prefix = $prefix;
    }

    public function read(string $id): array
    {
        $contents = $this->server->get($this->prefix.$id);

        return json_decode($contents, true);
    }

    public function exists(string $id): bool
    {
        return $this->server->exists($this->prefix.$id);
    }

    public function write(string $id, array $data): bool
    {
        $jsonString = json_encode($data);

        if (true !== $this->server->set($this->prefix.$id, $jsonString)) {
            throw new RuntimeException(sprintf('failed to write to redis server: %s', $this->server->getLastError()));
        }

        // set the timeout on key
        if ($this->expire) {
            $this->server->expire($this->prefix.$id, $this->expire);
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        return $this->server->delete($this->prefix.$id) > 0;
    }
}
