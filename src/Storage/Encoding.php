<?php declare(strict_types=1);

namespace Session\Storage;

if (!defined('JSON_THROW_ON_ERROR')) {
    define('JSON_THROW_ON_ERROR', 4194304);
}

if (!class_exists('JsonException')) {
    class JsonException extends \ErrorException
    {
    }
}

trait Encoding
{
    private function checkEncodingException(): void
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncodingException(json_last_error_msg() ?: 'Unknown JSON error code: '.json_last_error());
        }
    }

    protected function encode(array $data): string
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
            $this->checkEncodingException();
        } catch (JsonException $e) {
            throw new EncodingException($e->getMessage(), 0, $e);
        }
        return $json;
    }

    protected function decode(string $contents): array
    {
        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            $this->checkEncodingException();
        } catch (JsonException $e) {
            throw new EncodingException($e->getMessage(), 0, $e);
        }
        return $data;
    }
}
