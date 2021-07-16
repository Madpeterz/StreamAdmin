<?php

namespace YAPF\Cache;

abstract class CacheRequired
{
    protected bool $useErrorlog = false;

    protected function addErrorlog(string $message): void
    {
        if ($this->useErrorlog == true) {
            error_log($message);
        }
    }

    abstract protected function setupCache(): void;

    abstract protected function hasKey(string $key): bool;

    abstract protected function writeKeyReal(string $key, string $data, string $table, int $expiresUnixtime): bool;

    abstract protected function readKey(string $key): ?string;

    abstract protected function deleteKey(string $key): bool;
}
