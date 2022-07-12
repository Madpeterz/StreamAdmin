<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class Issue72 extends TestCase
{
    public function test_DetailsServer()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "DetailsServer";
        require "src/App/CronTab.php";
        $this->assertStringContainsString('"ticks":1,"sleep":0',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
    /**
     * @depends test_crontabClientAutoSuspend
     */
    public function test_DynamicNotecards()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "DynamicNotecards";
        require "src/App/CronTab.php";
        $this->assertStringContainsString('"ticks":1,"sleep":0',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
}

