<?php

namespace Session;

class Session implements SessionInterface {

	/**
	 * Storage handler
	 *
	 * @var object StorageInterface
	 */
	protected $storage;

	/**
	 * Avoid namespace collisions
	 *
	 * @var string
	 */
	protected $sessionPrefix;

	/**
	 * Session constructor
	 *
	 * @param object StorageInterface
	 * @param string
	 */
	public function __construct(StorageInterface $storage = null, $sessionPrefix = '_') {
		$this->storage = null === $storage ? new NativeStorage : $storage;
		$this->sessionPrefix = $sessionPrefix;
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
		$array = $this->get($key, []);

		$array[] = $value;

		$this->put($key, $array);
	}

	/**
	 * Flash a key value to be access in the next request
	 *
	 * @param string
	 * @param mixed
	 */
	public function putFlash($key, $value) {
		// save key in array to be rotated out
		$this->push($this->sessionPrefix . 'in', $key);

		// save the key value
		$this->put($this->sessionPrefix . 'flash.'.$key, $value);
	}

	/**
	 * Get value of a flash key
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function getFlash($key, $default = null) {
		return $this->get($this->sessionPrefix . 'flash.'.$key, $default);
	}

	/**
	 * Copy array of flash keys from `in` to `out`
	 * and remove the `in` array
	 */
	public function rotate() {
		// remove old keys
		foreach($this->get($this->sessionPrefix . 'out', []) as $key) {
			$this->remove($this->sessionPrefix . 'flash.'.$key);
		}

		// if we have a new in key array
		if($this->has($this->sessionPrefix . 'in')) {
			// copy it to the out key array for reading
			$this->put($this->sessionPrefix . 'out', $this->get($this->sessionPrefix . 'in'));

			// remove the old in key array once its been copied
			$this->remove($this->sessionPrefix . 'in');
		}
		// or just remove if we have nothing to copy in
		else {
			$this->remove($this->sessionPrefix . 'out');
		}
	}

}
