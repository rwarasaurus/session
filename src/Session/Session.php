<?php

namespace Session;

use Session\Storage\Native;
use Session\Contracts\Storage as StorageInterface;

class Session {

	protected $storage;

	public function __construct(StorageInterface $storage = null) {
		$this->storage = null === $storage ? new Native : $storage;
	}

	public function __call($method, array $args) {
		return call_user_func_array([$this->storage, $method], $args);
	}

	public function push($key, $value) {
		$array = $this->get($key, []);

		$array[] = $value;

		$this->put($key, $array);
	}

	public function putFlash($key, $value) {
		$this->push('in', $key);
		$this->put('_flash.'.$key, $value);
	}

	public function getFlash($key, $default = null) {
		return $this->get('_flash.'.$key, $default);
	}

	public function rotate() {
		foreach($this->get('out', []) as $key) {
			$this->remove('_flash.'.$key);
		}

		if($this->has('in')) {
			$this->put('out', $this->get('in'));
		}
		else {
			$this->remove('out');
		}

		$this->remove('in');
	}

}
