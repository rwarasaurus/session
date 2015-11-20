<?php

namespace Session;

interface FlashInterface {

	/**
	 * Flash a key value to be access in the next request
	 *
	 * @param string
	 * @param mixed
	 */
	public function putFlash($key, $value);

	/**
	 * Get value of a flash key
	 *
	 * @param string
	 */
	public function getFlash($key);

	/**
	 * Copy array of flash keys from `in` to `out`
	 * and remove the `in` array
	 */
	public function rotate();

}
