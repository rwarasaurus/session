<?php

namespace Session;

/**
 * Native session storage that uses the $_SESSION global as storage
 */
class NativeStorage implements StorageInterface {

	/**
	 * Creates a new instance, shock!
	 */
	public function __construct(\SessionHandlerInterface $handler = null, array $options = []) {
		$this->setOptions($options);
		$this->setSaveHandler(null === $handler ? new \SessionHandler : $handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOptions(array $options) {
		foreach($options as $key => $value) {
			ini_set('session.' . $key, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSaveHandler(\SessionHandlerInterface $handler) {
		session_set_save_handler($handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return session_id();
	}

	/**
	 * {@inheritdoc}
	 */
	public function start() {
		if($this->started()) {
			throw new \RuntimeException('Session already started');
		}

		if(false === session_start()) {
			throw new \RuntimeException('Failed to start session');
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function started() {
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close() {
		session_write_close();
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear() {
		$_SESSION = array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function regenerate($destroy = false) {
		session_regenerate_id($destroy);
	}

	/**
	 * {@inheritdoc}
	 */
	public function put($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null) {
		return $this->has($key) ? $_SESSION[$key] : $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key) {
		return array_key_exists($key, $_SESSION);
	}

}
