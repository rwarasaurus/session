<?php

namespace Session;

use Redis;
use InvalidArgumentException;

class RedisStorage implements StorageInterface
{
    protected $server;

    protected $expire;

    protected $prefix;

    public function __construct(Redis $server, int $expire = 3600, string $prefix = 'sess_')
    {
        if($expire < 1) {
            throw new InvalidArgumentException('expire time must be greater than zero');
        }

        if(is_numeric(substr($prefix, 0, 1))) {
            throw new InvalidArgumentException('prefix can not start with a number');
        }

        $this->server = $server;
        $this->expire = $expire;
        $this->prefix = $prefix;
    }

    public function read(string $id): array
    {
        $contents = $this->server->get($this->prefix.$id);

        if(empty($contents)) {
            return [];
        }

        return json_decode($contents, true);
    }

    public function write(string $id, array $data): bool
    {
        $jsonString = json_encode($data);

        return $this->server->set($this->prefix.$id, $jsonString, $this->expire);
    }

    public function destroy(string $id): bool
    {
        return $this->server->delete($this->prefix.$id) > 0;
    }
}
