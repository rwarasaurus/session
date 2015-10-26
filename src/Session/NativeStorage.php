<?php

namespace Session;

class NativeStorage implements StorageInterface {

	public function __construct(\SessionHandlerInterface $handler = null, array $options = []) {
		$this->setOptions($options);
		$this->setSaveHandler(null === $handler ? new \SessionHandler : $handler);
	}

	public function setOptions(array $options) {
		foreach($options as $key => $value) {
			ini_set('session.'.$key, $value);
		}
	}

	public function setSaveHandler(\SessionHandlerInterface $handler) {
		session_set_save_handler($handler);
	}

	public function id() {
		return session_id();
	}

	public function start() {
		if(session_status() === PHP_SESSION_ACTIVE) {
			throw new \RuntimeException('Session already started');
		}

		if( ! session_start()) {
			throw new \RuntimeException('Failed to start session');
		}

		return true;
	}

	public function close() {
		session_write_close();
	}

	public function clear() {
		$_SESSION = array();
	}

	public function regenerate($destroy = false) {
		session_regenerate_id($destroy);
	}

	public function put($key, $value) {
		$_SESSION[$key] = $value;
	}

	public function get($key, $default = null) {
		return $this->has($key) ? $_SESSION[$key] : $default;
	}

	public function remove($key) {
		unset($_SESSION[$key]);
	}

	public function has($key) {
		return array_key_exists($key, $_SESSION);
	}

}
