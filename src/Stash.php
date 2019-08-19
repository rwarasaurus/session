<?php declare(strict_types=1);

namespace Session;

trait Stash
{
    /**
     * @var string
     */
    protected $stashInKey = '_stash_in';

    /**
     * @var string
     */
    protected $stashOutKey = '_stash_out';

    /**
     * Rotate session stash data for the next request.
     *
     * @return SessionInterface
     */
    public function rotate(): SessionInterface
    {
        $this->data[$this->stashOutKey] = [];

        if (array_key_exists($this->stashInKey, $this->data)) {
            $this->data[$this->stashOutKey] = $this->data[$this->stashInKey];
            unset($this->data[$this->stashInKey]);
        }

        return $this;
    }

    /**
     * Get current stash key.
     *
     * @param string
     * @param string
     * @return mixed
     */
    public function getStash(string $key, $default = null)
    {
        return $this->data[$this->stashOutKey][$key] ?? $default;
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
        $this->data[$this->stashInKey][$key] = $value;

        return $this;
    }
}
