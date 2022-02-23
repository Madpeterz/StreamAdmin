<?php

namespace YAPF\Framework\Cache;

abstract class CacheRequired
{
    protected bool $useErrorlog = false;
    protected bool $connected = false; // set to true when a read/write passes ok

    protected function markConnected(): void
    {
        if ($this->connected == false) {
            $this->addErrorlog("Marking connected");
            $this->connected = true; // mark redis as connected
        }
    }

    protected function addErrorlog(string $message): void
    {
        if ($this->useErrorlog == true) {
            error_log($message);
        }
    }

    public function enableErrorLog(): void
    {
        $this->useErrorlog = true;
    }

    abstract protected function setupCache(): bool;

    abstract protected function hasKey(string $key): bool;

    abstract protected function writeKeyReal(string $key, string $data, int $expiresUnixtime): bool;

    abstract protected function readKey(string $key): ?string;

    abstract protected function deleteKey(string $key): bool;

    public function getKey(string $key): ?string
    {
        return $this->readKey($key);
    }

    public function setKey(string $key, string $value, int $expiresUnixtime): bool
    {
        return $this->writeKeyReal($key, $value, $expiresUnixtime);
    }
}
