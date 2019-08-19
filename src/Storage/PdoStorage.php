<?php declare(strict_types=1);

namespace Session\Storage;

use DateTimeImmutable;
use PDO;

class PdoStorage implements StorageInterface
{
    use Encoding;

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
        $expires = DateTimeImmutable::createFromFormat('U', time() - $this->expire);
        $stm = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE last_active < ?', $this->table));
        $stm->execute([
            $expires->format('Y-m-d H:i:s'),
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

        return $this->decode($contents);
    }

    public function exists(string $id): bool
    {
        $stm = $this->pdo->prepare(sprintf('SELECT COUNT(id) FROM %s WHERE id = ?', $this->table));
        $stm->execute([$id]);

        return $stm->fetchColumn() > 0;
    }

    public function write(string $id, array $data): bool
    {
        $now = new DateTimeImmutable();
        $contents = $this->encode($data);

        if ($this->exists($id)) {
            $stm = $this->pdo->prepare(sprintf('UPDATE %s SET last_active = ?, data = ? WHERE id = ?', $this->table));
            $stm->execute([
                $now->format('Y-m-d H:i:s'),
                $contents,
                $id,
            ]);
        } else {
            $stm = $this->pdo->prepare(sprintf('INSERT INTO %s (id, last_active, data) VALUES(?, ?, ?)', $this->table));
            $stm->execute([
                $id,
                $now->format('Y-m-d H:i:s'),
                $contents,
            ]);
        }

        return $stm->rowCount() > 0;
    }

    public function destroy(string $id): bool
    {
        $stm = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE id = ?', $this->table));
        $stm->execute([
            $id,
        ]);

        return $stm->rowCount() > 0;
    }
}
