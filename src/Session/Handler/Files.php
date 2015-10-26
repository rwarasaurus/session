<?php

namespace Session\Handler;

class Files implements \SessionHandlerInterface {

	protected $path;

	public function destroy($session_id) {
		$filepath = $this->path.'/'.$session_id;

		if(is_file($filepath)) {
			return unlink($filepath);
		}

		return true;
	}

	public function gc($maxlifetime) {
		$fi = new \FilesystemIterator($this->path, \FilesystemIterator::SKIP_DOTS);
		$now = time();

		foreach($fi as $file) {
			if($file->getMTime() + $maxlifetime < $now) {
				unlink($file->getPathname());
			}
		}

		return true;
	}

	public function open($save_path, $session_id) {
		$this->path = $save_path;

		if(false === is_dir($this->path)) {
			mkdir($this->path);
		}

		return is_file($this->path);
	}

	public function close() {
		return true;
	}

	public function read($session_id) {
		$filepath = $this->path.'/'.$session_id;

		if(is_file($filepath)) {
			return file_get_contents($filepath);
		}

		return '';
	}

	public function write($session_id, $session_data) {
		return false !== file_put_contents($this->path.'/'.$session_id, $session_data);
	}

}
