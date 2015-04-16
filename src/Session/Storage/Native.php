<?php

namespace Session\Storage;

use RuntimeException;
use Session\Handler\Native as NativeHandler;
use Session\Contracts\Handler as HandlerInterface;
use Session\Contracts\Storage as StorageInterface;

class Native implements StorageInterface {

	public function __construct(HandlerInterface $handler = null, array $options = []) {
		session_register_shutdown();
		$this->setOptions($options);
		$this->setSaveHandler(null === $handler ? new NativeHandler : $handler);
	}

	public function setOptions(array $options) {
		$valid = array('save_path', 'name');
		$options = array_intersect_key($options, array_fill_keys($valid, null));

		foreach($options as $key => $value) {
			ini_set('session.'.$key, $value);
		}
	}

	public function setSaveHandler(HandlerInterface $handler) {
		session_set_save_handler($handler, false);
	}

	public function id() {
		return session_id();
	}

	public function start() {
		if(function_exists('session_status') and session_status() === PHP_SESSION_ACTIVE) {
			throw new RuntimeException('Session already started');
		}

		if(isset($_SESSION) and session_id()) {
			throw new RuntimeException('Session already started ($_SESSION is set)');
		}

		if( ! session_start()) {
			throw new RuntimeException('Failed to start session');
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
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
	}

	public function remove($key) {
		unset($_SESSION[$key]);
	}

}
