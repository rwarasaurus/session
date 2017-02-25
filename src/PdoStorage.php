<?php

namespace Session;

use DateTime;
use DateInterval;
use PDO;
use InvalidArgumentException;

class PdoStorage implements StorageInterface
{
    protected $pdo;

    protected $expire;

    protected $table;

    public function __construct(PDO $pdo, string $table, int $expire = 3600)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->expire = $expire;
    }

    public function purge()
    {
        $expires = new DateTime;
        $exires->sub(new DateInterval(sprintf('PT%dS', $this->expires)));
        $stm = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE last_active < ?', $this->table));
        $stm->execute([
            $exires->format('Y-m-d H:i:s')
        ]);
    }

    public function read(string $id): array
    {
        $stm = $this->pdo->prepare(sprintf('SELECT data FROM %s WHERE id = ?', $this->table));
        $stm->execute([$id]);

        $contents = $stm->fetchColumn();

        if (!$contents) {
            return [];
        }

        return json_decode($contents, true);
    }

    public function exists(string $id): bool
    {
        $stm = $this->pdo->prepare(sprintf('SELECT COUNT(id) FROM %s WHERE id = ?', $this->table));
        $stm->execute([$id]);

        return $stm->fetchColumn() > 0;
    }

    public function write(string $id, array $data): bool
    {
        $now = new DateTime;
        $jsonString = json_encode($data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Error encoding data to json string: ' . json_last_error_msg());
        }

        if($this->exists($id)) {
            $stm = $this->pdo->prepare(sprintf('UPDATE %s SET last_active = ?, data = ? WHERE id = ?', $this->table));
            $stm->execute([
                $now->format('Y-m-d H:i:s'),
                $jsonString,
                $id,
            ]);
        }
        else {
            $stm = $this->pdo->prepare(sprintf('INSERT INTO %s (id, last_active, data) VALUES(?, ?, ?)', $this->table));
            $stm->execute([
                $id,
                $now->format('Y-m-d H:i:s'),
                $jsonString,
            ]);
        }

        return $stm->rowCount() > 0;
    }

    public function destroy(string $id): bool
    {
        $stm = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE id = ?', $this->table));
        $stm->execute([
            $id
        ]);

        return $stm->rowCount() > 0;
    }
}
