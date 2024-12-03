<?php

namespace App\Helpers;

class StaffPasswordHashReply
{
    public function __construct(readonly public string $phash = "", readonly public string $psalt = "")
    {
    }
}
