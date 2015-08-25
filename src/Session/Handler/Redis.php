<?php

namespace Session\Handler;

use Session\Contracts\Handler;

class Redis implements Handler {

	protected $server;

	protected $ttl;

	public function __construct(\Redis $server, $ttl = 7200) {
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
		return $this->server->get($session_id);
	}

	public function write($session_id, $session_data) {
		$this->server->set($session_id, $session_data, $this->ttl);
	}

}
