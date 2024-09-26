<?php

namespace App\Helpers;

class BasicReply
{
    public function __construct(readonly public bool $status = false, readonly public string $message = "not set")
    {
    }
}
