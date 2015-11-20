<?php

namespace Session;

class Session implements SessionInterface, FlashInterface {

	/**
	 * Storage handler
	 *
	 * @var object StorageInterface
	 */
	protected $storage;

	/**
	 * Session key prefix to avoid namespace collisions
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Session flash in key
	 *
	 * @var string
	 */
	protected $flashInKey;

	/**
	 * Session flash out key
	 *
	 * @var string
	 */
	protected $flashOutKey;

	/**
	 * Session flash variable key prefix
	 *
	 * @var string
	 */
	protected $flashVarKey;

	/**
	 * Session constructor
	 *
	 * @param object StorageInterface
	 * @param string
	 */
	public function __construct(StorageInterface $storage = null, $prefix = 'flash') {
		$this->storage = null === $storage ? new NativeStorage : $storage;
		$this->setPrefix($prefix);
	}

	/**
	 * Sets the internal session key prefix to avoid namespace collisions
	 *
	 * @param string
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		$this->flashInKey = $this->prefix . '.in';
		$this->flashOutKey = $this->prefix . '.out';
		$this->flashVarKey = $this->prefix . '.';
	}

	/**
	 * Call method on the storage handler
	 *
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($method, array $args) {
		return call_user_func_array([$this->storage, $method], $args);
	}

	/**
	 * Stack a value in a array
	 *
	 * @param string
	 * @param mixed
	 */
	public function push($key, $value) {
		$stack = $this->get($key, []);

		$stack[] = $value;

		$this->put($key, $stack);
	}

	/**
	 * {@inheritdoc}
	 */
	public function putFlash($key, $value) {
		// save key in array to be rotated out
		$this->push($this->flashInKey, $key);

		// save the key value
		$this->put($this->flashVarKey . $key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFlash($key, $default = null) {
		return $this->get($this->flashVarKey . $key, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate() {
		// remove old keys
		foreach($this->get($this->flashOutKey, []) as $key) {
			$this->remove($this->flashVarKey . $key);
		}

		// if we have something in the "in" key array
		if($this->has($this->flashInKey)) {
			// copy it to the "out" key array for reading
			$this->put($this->flashOutKey, $this->get($this->flashInKey));

			// remove the "in" key array
			return $this->remove($this->flashInKey);
		}

		// if theres nothing in the "in" key array remove the "out" key array
		return $this->remove($this->flashOutKey);
	}

}
