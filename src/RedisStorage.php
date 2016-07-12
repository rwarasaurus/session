<?php

namespace Session;

use Redis;

class RedisStorage implements StorageInterface
{
    protected $server;

    protected $ttl;

    public function __construct(Redis $server, int $ttl = 3600)
    {
        $this->server = $server;
        $this->ttl = $ttl;
    }

    public function read(string $id): array
    {
        $contents = $this->server->get($id);

        if(empty($contents)) {
            return [];
        }

        return json_decode($contents, true);
    }

    public function write(string $id, array $data): bool
    {
        $jsonString = json_encode($data);

        return $this->server->set($id, $jsonString, $this->ttl);
    }

    public function destroy(string $id): bool
    {
        return $this->server->delete($id) > 0;
    }
}
