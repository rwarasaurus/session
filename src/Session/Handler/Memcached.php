<?php

namespace Session\Handler;

use Session\Contracts\Handler;

class Memcached implements Handler {

	protected $memcache;

	protected $ttl;

	public function __construct(\Memcached $memcache, $ttl = 7200) {
		$this->memcache = $memcache;
		$this->ttl = $ttl;
	}

	public function destory($session_id) {
		$this->memcache->delete($session_id);

		return true;
	}

	public function gc($maxlifetime) {
		return true;
	}

	public function open($save_path, $session_id) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($session_id) {
		$data = $this->memcache->get($session_id);

		if($this->memcache->getResultCode() === Memcached::RES_NOTFOUND) {
			return '';
		}

		return $data;
	}

	public function write($session_id, $session_data) {
		$this->memcache->set($session_id, $session_data, $this->ttl);
	}

}
