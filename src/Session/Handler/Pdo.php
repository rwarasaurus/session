<?php

namespace Session\Handler;

class Pdo implements \SessionHandlerInterface {

	protected $pdo;

	protected $config;

	public function __construct(\PDO $pdo, array $config) {
		$this->pdo = $pdo;
		$this->config = $config;
	}

	public function destroy($session_id) {
		$sql = sprintf('DELETE FROM %s WHERE session_id = ?', $this->config['table']);
		$stm = $this->pdo->prepare($sql);
		return $stm->execute([$session_id]);
	}

	public function gc($maxlifetime) {
		$sql = sprintf('DELETE FROM %s WHERE (session_time + ?) < ?', $this->config['table']);
		$stm = $this->pdo->prepare($sql);

		$now = time();
		return $stm->execute([$maxlifetime, $now]);
	}

	public function open($save_path, $session_id) {
		$session_time = time();

		$sql = sprintf('INSERT INTO %s (session_id, session_data, session_time) VALUES(?, ?, ?)', $this->config['table']);
		$stm = $this->pdo->prepare($sql);
		return $stm->execute([$session_id, '', $session_time]);
	}

	public function close() {
		return true;
	}

	public function read($session_id) {
		$sql = sprintf('SELECT session_data FROM %s WHERE session_id = ?', $this->config['table']);
		$stm = $this->pdo->prepare($sql);
		$stm->execute([$session_id]);

		return $stm->fetchColumn();
	}

	public function write($session_id, $session_data) {
		$sql = sprintf('UPDATE %s SET session_data = ? WHERE session_id = ?', $this->config['table']);
		$stm = $this->pdo->prepare($sql);
		return $stm->execute([$session_data, $session_id]);
	}

}
