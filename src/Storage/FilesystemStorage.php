<?php declare(strict_types=1);

namespace Session\Storage;

use League\Flysystem\FilesystemInterface;

class FilesystemStorage implements StorageInterface
{
    use Encoding;

    protected $fs;

    protected $expire;

    public function __construct(FilesystemInterface $fs, int $expire = 3600)
    {
        $this->fs = $fs;
        $this->expire = $expire;
    }

    public function purge()
    {
        foreach ($this->fs->listContents() as $object) {
            if ($object->isFile() && $this->expired($object->getTimestamp())) {
                $object->delete();
            }
        }
    }

    protected function expired(int $last, ?int $time = null): bool
    {
        $time = $time ?: time();
        return ($last + $this->expire) < $time;
    }

    protected function filename(string $id): string
    {
        return sprintf('%s.sess', $id);
    }

    public function read(string $id): array
    {
        if (!$this->exists($id)) {
            return [];
        }

        $file = $this->filename($id);
        $contents = $this->fs->read($file);

        return $this->decode($contents);
    }

    public function exists(string $id): bool
    {
        $file = $this->filename($id);
        return $this->fs->has($file) && !$this->expired($this->fs->getTimestamp($file));
    }

    public function write(string $id, array $data): bool
    {
        $file = $this->filepath($id);

        $contents = $this->encode($data);

        $this->fs->write($file, $contents);

        return true;
    }

    public function destroy(string $id): bool
    {
        if ($this->exists($id)) {
            $file = $this->filepath($id);
            $this->fs->delete($file);
        }

        return !$this->exists($id);
    }
}
