<?php

namespace Session\Storage;

use RuntimeException;
use Session\Handler\Native as NativeHandler;
use Session\Contracts\Handler as HandlerInterface;
use Session\Contracts\Storage as StorageInterface;

class Native implements StorageInterface {

	protected $validOptions = [
		'save_path',
		'name',
		'gc_probability',
		'gc_divisor',
		'gc_maxlifetime',
		'serialize_handler',
		'cookie_lifetime',
		'cookie_path',
		'cookie_domain',
		'cookie_secure',
		'cookie_httponly',
		'use_strict_mode',
		'use_cookies',
		'use_only_cookies',
		'referer_check',
		'entropy_file',
		'entropy_length',
		'cache_limiter',
		'cache_expire',
		'hash_function',
		'hash_bits_per_character',
		'upload_progress.enabled',
		'upload_progress.cleanup',
		'upload_progress.prefix',
		'upload_progress.name',
		'upload_progress.freq',
		'upload_progress.min_freq',
		'lazy_write',
	];

	public function __construct(HandlerInterface $handler = null, array $options = []) {
		session_register_shutdown();
		$this->setOptions($options);
		$this->setSaveHandler(null === $handler ? new NativeHandler : $handler);
	}

	public function setOptions(array $userOptions) {
		$options = array_intersect_key($userOptions, array_fill_keys($this->validOptions, null));

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
		if(session_status() === PHP_SESSION_ACTIVE) {
			throw new RuntimeException('Session already started');
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
		return $this->has($key) ? $_SESSION[$key] : $default;
	}

	public function remove($key) {
		unset($_SESSION[$key]);
	}

	public function has($key) {
		return array_key_exists($key, $_SESSION);
	}

}
