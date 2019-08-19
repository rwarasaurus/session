<?php declare(strict_types=1);

namespace Session;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class Session implements SessionInterface
{
    use Options, Stash;

    /**
     * @var CookiesInterface
     */
    protected $cookies;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var boolean
     */
    protected $started = false;

    /**
     * Construct the session!
     *
     * @param CookiesInterface
     * @param StorageInterface
     * @param array
     */
    public function __construct(CookiesInterface $cookies, Storage\StorageInterface $storage, array $options = [])
    {
        $this->cookies = $cookies;
        $this->storage = $storage;
        $this->setOptions($options);
        $this->id = $this->generate();
    }

    /**
     * Generate a secure random session ID.
     *
     * @return string
     */
    protected function generate(): string
    {
        $entropy = (int) $this->getOption('entropy');

        if ($entropy < 32) {
            $entropy = 32;
        }

        return \bin2hex(\random_bytes($entropy));
    }

    /**
     * Get the session ID.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get the cookie name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->getOption('name');
    }

    /**
     * Migrate the session ID.
     *
     * @return SessionInterface
     */
    public function migrate(): SessionInterface
    {
        $this->id = $this->generate();

        return $this;
    }

    /**
     * Clear the session data.
     *
     * @return SessionInterface
     */
    public function clear(): SessionInterface
    {
        $this->data = [];

        return $this;
    }

    /**
     * Destroy the session data and migrate session ID.
     *
     * @return SessionInterface
     */
    public function destroy(): SessionInterface
    {
        $this->storage->destroy($this->id);

        return $this->clear()->migrate();
    }

    /**
     * Start the session.
     */
    public function start()
    {
        $name = $this->name();

        // try and resume session from cookie
        if ($this->cookies->has($name)) {
            $this->id = $this->cookies->get($name);
            if ($this->storage->exists($this->id)) {
                $this->data = $this->storage->read($this->id);
            }
        }

        $this->started = true;
    }

    /**
     * Check if the session has been started.
     *
     * @return bool
     */
    public function started(): bool
    {
        return $this->started;
    }

    /**
     * Commit data to storage.
     */
    protected function commit()
    {
        if (!$this->started) {
            throw new SessionException('Session has not been started');
        }

        $this->storage->write($this->id, $this->data);
    }

    /**
     * Close the session.
     */
    public function close()
    {
        if (!$this->started) {
            throw new SessionException('Session has not been started');
        }

        $this->commit();

        $this->started = false;
    }

    /**
     * Return the cookie header.
     *
     * @return string
     */
    public function cookie(): string
    {
        $pairs = [
            sprintf('%s=%s', $this->name(), $this->id),
        ];

        if ($expire = $this->getOption('expire')) {
            $gmdate = new DateTimeImmutable();
            $gmdate->setTimezone(new DateTimeZone('GMT'));
            $gmdate->add(new DateInterval(sprintf('PT%uS', $expire)));
            $pairs[] = sprintf(
                'Expires=%s; Max-Age=%u',
                $gmdate->format('D, d-M-Y H:i:s T'),
                $expire
            );
        }

        if ($path = $this->getOption('path')) {
            $pairs[] = sprintf('Path=%s', $path);
        }

        if ($domain = $this->getOption('domain')) {
            $pairs[] = sprintf('Domain=%s', $domain);
        }

        if ($this->getOption('secure')) {
            $pairs[] = 'Secure';
        }

        if ($this->getOption('httponly')) {
            $pairs[] = 'HttpOnly';
        }

        if ($sameSite = $this->getOption('samesite')) {
            $pairs[] = sprintf('SameSite=%s', $sameSite);
        }

        return implode('; ', $pairs);
    }

    /**
     * Check if the session has a key.
     *
     * @param string
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a session key with a fallback.
     *
     * @param string
     * @param string
     *
     * @return string
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Get all session data.
     *
     * @return array
     */
    public function all(): array
    {
        return array_diff_key($this->data, array_flip(['_stash_in', '_stash_out']));
    }

    /**
     * Store a key-value in the session.
     *
     * @param string
     * @param string
     *
     * @return SessionInterface
     */
    public function put(string $key, $value): SessionInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Remove a key from the session.
     *
     * @param string
     *
     * @return SessionInterface
     */
    public function remove(string $key): SessionInterface
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }
}
