<?php declare(strict_types=1);

namespace Session\Storage;

class RedisStorage implements StorageInterface
{
    use Encoding;

    protected $client;

    protected $expire;

    protected $prefix;

    public function __construct($client, int $expire = 3600, string $prefix = 'sess_')
    {
        if (!$client instanceof \Redis && !$client instanceof \Predis\Client) {
            throw new StorageException('Client must be a instance of Redis or Predis\Client');
        }

        if (is_numeric(substr($prefix, 0, 1))) {
            throw new StorageException('Prefix can not start with a number');
        }

        $this->client = $client;
        $this->expire = $expire;
        $this->prefix = $prefix;
    }

    public function read(string $id): array
    {
        $contents = $this->client->get($this->prefix.$id);

        return $this->decode($contents);
    }

    public function exists(string $id): bool
    {
        return $this->client->exists($this->prefix.$id);
    }

    public function write(string $id, array $data): bool
    {
        $contents = $this->encode($data);

        if (true !== $this->client->set($this->prefix.$id, $contents)) {
            throw new StorageException(sprintf('failed to write to redis server: %s', $this->client->getLastError()));
        }

        // set the timeout on key
        if ($this->expire) {
            $this->client->expire($this->prefix.$id, $this->expire);
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        return $this->client->delete($this->prefix.$id) > 0;
    }
}
