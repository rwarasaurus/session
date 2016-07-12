<?php

namespace Session;

use Psr\Http\Message\ResponseInterface;

class Session implements SessionInterface
{
    protected $cookies;

    protected $storage;

    protected $data = [];

    protected $id;

    protected $options;

    protected $started = false;

    public function __construct(CookiesInterface $cookies, StorageInterface $storage, array $options = [])
    {
        $this->cookies = $cookies;
        $this->storage = $storage;
        $this->setOptions($options);
    }

    protected function generate(): string
    {
        return bin2hex(random_bytes($this->options['entropy']));
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->options['name'];
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function setOptions(array $options) {
        $defaults = [
            'name' => 'PHPSESSID',
            'expire' => 0,
            'path' => '',
            'domain' => '',
            'secure' => 0,
            'httponly' => 1,
            'entropy' => 32,
        ];
        $this->options = array_merge($defaults, $options);
    }

    public function migrate(): SessionInterface
    {
        $this->id = $this->generate();

        return $this;
    }

    public function destroy(): SessionInterface
    {
        $this->storage->destroy($this->id);
        $this->data = [];

        return $this->migrate();
    }

    public function start()
    {
        if ($this->cookies->has($this->options['name'])) {
            $this->id = $this->cookies->get($this->options['name']);
        } else {
            $this->id = $this->generate();
        }

        $this->data = $this->storage->read($this->id);

        $this->started = true;
    }

    public function started(): bool
    {
        return $this->started;
    }

    protected function commit()
    {
        if (!$this->started) {
            throw new \RuntimeException('Session has not been started');
        }

        $this->storage->write($this->id, $this->data);
    }

    public function close(ResponseInterface $response = null)
    {
        if (!$this->started) {
            throw new \RuntimeException('Session has not been started');
        }

        $this->commit();

        if($response) {
            return $response->withAddedHeader('Set-Cookie', $this->cookie());
        }
    }

    public function cookie(): string
    {
        $pairs = [
            sprintf('%s=%s', $this->options['name'], $this->id),
        ];

        if ($this->options['expire']) {
            $gmdate = new \DateTime();
            $gmdate->setTimezone(new \DateTimeZone('UTC'));
            $format = sprintf('PT%dS', $this->options['expire']);
            $gmdate->add(new \DateInterval($format));
            $pairs[] = sprintf('expires=%s', $gmdate->format(\DateTime::COOKIE));
        }

        if ($this->options['path']) {
            $pairs[] = sprintf('path=%s', $this->options['path']);
        }

        if ($this->options['domain']) {
            $pairs[] = sprintf('domain=%s', $this->options['domain']);
        }

        if ($this->options['secure']) {
            $pairs[] = 'secure';
        }

        if ($this->options['httponly']) {
            $pairs[] = 'HttpOnly';
        }

        return implode('; ', $pairs);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function all(): array
    {
        return array_intersect_key(['_stash_in', '_stash_out'], $this->data);
    }

    public function put(string $key, $value): SessionInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function remove(string $key): SessionInterface
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    public function rotate(): SessionInterface
    {
        $this->data['_stash_out'] = [];

        if (array_key_exists('_stash_in', $this->data)) {
            $this->data['_stash_out'] = $this->data['_stash_in'];
            unset($this->data['_stash_in']);
        }

        return $this;
    }

    public function getStash(string $key, $default = null)
    {
        return $this->data['_stash_out'][$key] ?? $default;
    }

    public function putStash(string $key, $value)
    {
        $this->data['_stash_in'][$key] = $value;
    }
}
