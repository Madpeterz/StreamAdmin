<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class Issue72 extends TestCase
{
    public function test_crontabClientAutoSuspend()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "ClientAutoSuspend";
        include "src/App/CronJob/CronTab.php";
        $this->assertStringEndsWith('"ok":1,"failed":0,"ticks":1,"sleeped":0}',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
    public function test_DetailsServer()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "DetailsServer";
        include "src/App/CronJob/CronTab.php";
        $this->assertStringEndsWith('"ok":1,"failed":0,"ticks":1,"sleeped":0}',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
    public function test_ApiRequestsServer()
    {       
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "ApiRequests";
        include "src/App/CronJob/CronTab.php";
        $this->assertStringEndsWith('"ok":1,"failed":0,"ticks":1,"sleeped":0}',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }
}

