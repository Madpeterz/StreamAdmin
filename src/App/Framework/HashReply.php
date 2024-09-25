<?php

namespace App\Framework;

class HashReply
{
    public readonly bool $status;
    public readonly string $message;
    public readonly bool $newSalt;
    public readonly string $saltValue;
    public readonly string $phash;
    public function __construct(
        string $message,
        bool $status = false,
        bool $newSalt = false,
        string $saltValue = "",
        string $phash = ""
    ) {
        $this->message = $message;
        $this->status = $status;
        $this->newSalt = $newSalt;
        $this->saltValue = $saltValue;
        $this->phash = $phash;
    }
}
