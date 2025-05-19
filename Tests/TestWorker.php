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
    protected function makeSLconnection(
        string $module,
        string $area,
        string $avataruuid,
        string $avatarname,
        string $objectuuid,
        string $objectname,
        string $regionname,
        string $objecttype,
        int $apicode=1) : void
    {
        $this->resetPost();
        global $system;
        $_SERVER["HTTP_X_SECONDLIFE_SHARD"] = "Production";
        $system->setModule($module);
        $system->setArea($area);
        $required_sl = [
            "version" => "2.0.1.1",
            "mode" => "api",
            "objectuuid" => $objectuuid,
            "regionname" => $regionname,
            "ownerkey" => $avataruuid,
            "ownername" => $avatarname,
            "pos" => "1,1,1",
            "objectname" => $objectname,
            "objecttype" => $objecttype,
        ];
        $staticpart = $module;
        $staticpart .= $area;
        foreach($required_sl as $key => $value)
        {
            $_POST[$key] = $value;
            $staticpart .= $value;
        }
        $unixtime = time();
        $_POST["unixtime"] = $unixtime;
        $linkcode = $system->getSlConfig()->getHudLinkCode();
        if($apicode == 2)
        {
            $required_sl["mode"] = "object";
            $linkcode = $system->getSlConfig()->getSlLinkCode();
        }
        else if($apicode == 2)
        {
            $required_sl["mode"] = "bot";
            $linkcode = $system->getSlConfig()->getHttpInboundSecret();
        }
        $hash = $unixtime."".$staticpart . "" . $linkcode;
        $_POST["raw"] = $hash;
        $_POST["hash"] = sha1($hash);
    }
}

class SessionControlTesting extends SessionControl
{
    public function getOwnerLevel(): bool
    {
        return true;
    }
}