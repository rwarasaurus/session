<?php

namespace Session\Contracts;

interface Storage {
	public function id();
	public function start();
	public function close();
	public function regenerate($destroy = false);
	public function clear();
	public function get($key);
	public function put($key, $value);
	public function remove($key);
}
