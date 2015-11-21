<?php

namespace Session\Handler;

class Files implements \SessionHandlerInterface {

	protected $path;

	protected function filepath($session_id) {
		if(null === $this->path) {
			throw new \RuntimeException('Session path has not been set, make sure you call open() first');
		}

		if(false === is_dir($this->path)) {
			throw new \RuntimeException(sprintf('Session path does not exist "%s"', $this->path));
		}

		if(false === is_writable($this->path)) {
			throw new \RuntimeException(sprintf('Session path is not writable "%s"', $this->path));
		}

		return sprintf('%s/sess_%s', $this->path, $session_id);
	}

	public function destroy($session_id) {
		$filepath = $this->filepath($session_id);

		return is_file($filepath) ? unlink($filepath) : true;
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

		$filepath = $this->filepath($session_id);

		return touch($filepath);
	}

	public function close() {
		return true;
	}

	public function read($session_id) {
		$filepath = $this->filepath($session_id);

		return file_get_contents($filepath);
	}

	public function write($session_id, $session_data) {
		$filepath = $this->filepath($session_id);

		return false !== file_put_contents($filepath, $session_data);
	}

}
