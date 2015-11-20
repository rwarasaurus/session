<?php

namespace Session;

use SessionHandlerInterface;

interface StorageInterface {

	/**
	 * Set session options
	 */
	public function setOptions(array $options);

	/**
	 * Sets the save handler
	 */
	public function setSaveHandler(SessionHandlerInterface $handler);

	/**
	 * Return the current session ID
	 */
	public function id();

	/**
	 * Creates a session
	 */
	public function start();

	/**
	 * Check if the session has already been created
	 */
	public function started();

	/**
	 * End the current session and store session data
	 */
	public function close();

	/**
	 * Update the current session id with a newly generated one
	 */
	public function regenerate($destroy = false);

	/**
	 * Discard session array changes
	 */
	public function clear();

	/**
	 * Returns a value from the SESSION global
	 */
	public function get($key);

	/**
	 * Sets a value on the SESSION global
	 */
	public function put($key, $value);

	/**
	 * Unsets a value from the SESSION global
	 */
	public function remove($key);

	/**
	 * Tests if a key exists in the SESSION global
	 */
	public function has($key);

}
