<?php

namespace StreamAdminR7;

use Tests\Mytest;

class Issue72 extends Mytest
{
    public function test_DetailsServer()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "DetailsServer";
        require "src/App/CronTab.php";
        $this->assertStringContainsString('"ticks":1,',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
    /**
     * @depends test_DetailsServer
     */
    public function test_DynamicNotecards()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "DynamicNotecards";
        require "src/App/CronTab.php";
        $this->assertStringContainsString('"ticks":1,',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
}

