<?php

namespace Tests;
use App\Framework\SessionControl;
use PHPUnit\Framework\TestCase;

class TestWorker extends TestCase
{
    public function resetPost()
    {
        global $_POST;
        $_POST = [];
    }
    public static function setUpBeforeClass(): void
    {
        global $system;
        $result = $system->getSQL()->rawSQL("Tests/test.reset.sql"); // wipe DB
    }
}

class SessionControlTesting extends SessionControl
{
    public function getOwnerLevel(): bool
    {
        return true;
    }
}