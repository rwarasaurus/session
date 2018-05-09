<?php

namespace Session;

use DateTime;
use DateTimeZone;
use DateInterval;
use RuntimeException;
use InvalidArgumentException;

class Session implements SessionInterface
{
    protected $cookies;

    protected $storage;

    protected $data = [];

    protected $id;

    protected $options;

    protected $started = false;

    /**
     * Contsruct the session!
     * 
     * @param CookiesInterface
     * @param StorageInterface
     * @param array
     */
    public function __construct(CookiesInterface $cookies, StorageInterface $storage, array $options = [])
    {
        $this->cookies = $cookies;
        $this->storage = $storage;
        $this->setOptions($options);
    }

    /**
     * Generate a secure random session ID.
     * 
     * @return string
     */
    protected function generate(): string
    {
        return bin2hex(random_bytes($this->options['entropy']));
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
        return $this->options['name'];
    }

    /**
     * Return the config options.
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the config options.
     * 
     * @param array
     */
    public function setOptions(array $options)
    {
        $defaults = [
            'name' => 'PHPSESSID',
            'expire' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => 0,
            'httponly' => 0,
            'samesite' => '',
            'entropy' => 32,
        ];

        $invalid = array_diff(array_keys($options), array_keys($defaults));

        if (!empty($invalid)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid session options: %s.',
                implode(', ', $invalid)
            ));
        }

        $this->options = array_merge($defaults, $options);
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
        $name = $this->options['name'];

        // try and resume session from cookie
        if ($this->cookies->has($name)) {
            $this->id = $this->cookies->get($name);
            if ($this->storage->exists($this->id)) {
                $this->data = $this->storage->read($this->id);
            }
        } else {
            // create a new session id
            $this->migrate();
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
            throw new RuntimeException('Session has not been started');
        }

        $this->storage->write($this->id, $this->data);
    }

    /**
     * Close the session.
     */
    public function close()
    {
        if (!$this->started) {
            throw new RuntimeException('Session has not been started');
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
            sprintf('%s=%s', $this->options['name'], $this->id),
        ];

        if ($this->options['expire']) {
            $gmdate = new DateTime();
            $gmdate->setTimezone(new DateTimeZone('GMT'));
            $format = sprintf('PT%dS', $this->options['expire']);
            $gmdate->add(new DateInterval($format));
            $pairs[] = sprintf(
                'Expires=%s; Max-Age=%d',
                $gmdate->format('D, d-M-Y H:i:s T'),
                $this->options['expire']
            );
        }

        if ($this->options['path']) {
            $pairs[] = sprintf('Path=%s', $this->options['path']);
        }

        if ($this->options['domain']) {
            $pairs[] = sprintf('Domain=%s', $this->options['domain']);
        }

        if ($this->options['secure']) {
            $pairs[] = 'Secure';
        }

        if ($this->options['httponly']) {
            $pairs[] = 'HttpOnly';
        }

        if ($this->options['samesite']) {
            $pairs[] = sprintf('SameSite=%s', $this->options['samesite']);
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

    /**
     * Rotate session stash data for the next request.
     * 
     * @return SessionInterface
     */
    public function rotate(): SessionInterface
    {
        $this->data['_stash_out'] = [];

        if (array_key_exists('_stash_in', $this->data)) {
            $this->data['_stash_out'] = $this->data['_stash_in'];
            unset($this->data['_stash_in']);
        }

        return $this;
    }

    /**
     * Get current stash key.
     * 
     * @param string
     * @param string
     *
     * @return string
     */
    public function getStash(string $key, $default = null)
    {
        return $this->data['_stash_out'][$key] ?? $default;
    }

    /**
     * Store a key-value in session stash for the next request.
     * 
     * @param string
     * @param string
     *
     * @return SessionInterface
     */
    public function putStash(string $key, $value): SessionInterface
    {
        $this->data['_stash_in'][$key] = $value;

        return $this;
    }
}
