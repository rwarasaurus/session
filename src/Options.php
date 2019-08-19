<?php declare(strict_types=1);

namespace Session;

trait Options
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $defaultOptions = [
        'name' => 'PHPSESSID',
        'expire' => '0',
        'path' => '/',
        'domain' => '',
        'secure' => '0',
        'httponly' => '0',
        'samesite' => '',
        'entropy' => '32',
    ];

    /**
     * Set the options.
     *
     * @param array
     */
    public function setOptions(array $options)
    {
        $invalid = array_diff_key($options, $this->defaultOptions);

        if (!empty($invalid)) {
            throw new SessionException(sprintf(
                'Invalid session options: %s.',
                implode(', ', $invalid)
            ));
        }

        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Return the options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Return the config options.
     *
     * @return array
     */
    public function getOption(string $name): string
    {
        if (!array_key_exists($name, $this->options)) {
            throw new SessionException(sprintf(
                'Unknown session option: %s.',
                $name
            ));
        }
        return $this->options[$name];
    }
}
