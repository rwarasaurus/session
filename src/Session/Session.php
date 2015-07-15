<?php

namespace Session;

use Session\Storage\Native as NativeStorageHandler;
use Session\Contracts\Storage as StorageInterface;

class Session {

	/**
	 * Storage handler
	 *
	 * @var object StorageInterface
	 */
	protected $storage;

	/**
	 * Session constructor
	 *
	 * @param object StorageInterface
	 */
	public function __construct(StorageInterface $storage = null) {
		$this->storage = null === $storage ? new NativeStorageHandler : $storage;
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
		$this->push('in', $key);

		// save the key value
		$this->put('_flash.'.$key, $value);
	}

	/**
	 * Get value of a flash key
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function getFlash($key, $default = null) {
		return $this->get('_flash.'.$key, $default);
	}

	/**
	 * Copy array of flash keys from `in` to `out`
	 * and remove the `in` array
	 */
	public function rotate() {
		// remove old keys
		foreach($this->get('out', []) as $key) {
			$this->remove('_flash.'.$key);
		}

		// if we have a new in key array
		if($this->has('in')) {
			// copy it to the out key array for reading
			$this->put('out', $this->get('in'));

			// remove the old in key array once its been copied
			$this->remove('in');
		}
		// or just remove if we have nothing to copy in
		else {
			$this->remove('out');
		}
	}

}
