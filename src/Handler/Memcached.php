<?php

namespace Session\Handler;

class Memcached implements \SessionHandlerInterface {

	protected $server;

	protected $ttl;

	public function __construct($server, $ttl = 7200) {
		$this->server = $server;
		$this->ttl = $ttl;
	}

	public function destroy($session_id) {
		$this->server->delete($session_id);

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
		$result = $this->server->get($session_id);

		// should always return a string
		if(false === $result) return '';

		return $result;
	}

	public function write($session_id, $session_data) {
		return $this->server->set($session_id, $session_data, $this->ttl);
	}

}
