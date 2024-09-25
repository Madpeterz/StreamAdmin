<?php

namespace Tests;

use App\Config;
use PHPUnit\Framework\TestCase;

abstract class Mytest extends TestCase
{
    protected function assertPreConditions(): void
    {
        global $system;
        $system = new Config();
        $system->setFolders("src", "");
        $system->run();
    }
    protected function assertPostConditions(): void
    {
        global $system;
        if(isset($system) == true) {
            if(is_object($system) == true) {
                $system->shutdown();
                $system=null;
                unset($system);
            }
        }
    }

}