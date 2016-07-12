<?php

namespace Session;

use Redis;

class RedisStorage implements StorageInterface
{
    protected $server;

    protected $ttl;

    protected $prefix;

    public function __construct(Redis $server, int $ttl = 3600, string $prefix = 'sess_')
    {
        $this->server = $server;
        $this->ttl = $ttl;
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

        return $this->server->set($this->prefix.$id, $jsonString, $this->ttl);
    }

    public function destroy(string $id): bool
    {
        return $this->server->delete($this->prefix.$id) > 0;
    }
}
