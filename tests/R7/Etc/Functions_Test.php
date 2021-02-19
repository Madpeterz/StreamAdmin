<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function test_expiredAgo()
    {
        $expiredAgo = expiredAgo(time()-20,true);
        $this->assertSame("20 secs",$expiredAgo);
        $expiredAgo = expiredAgo(time()-120,false);
        $this->assertSame("2 mins",$expiredAgo);
        $expiredAgo = expiredAgo(time()+120,false);
        $this->assertSame("Active",$expiredAgo);
    }

    public function test_is_checked()
    {
        $this->assertSame("",is_checked(false));
        $this->assertSame(" checked ",is_checked(true));
    }

    public function test_timeleftHoursAndDays()
    {
        global $unixtime_week, $unixtime_min, $unixtime_hour;
        $timeleft = timeleftHoursAndDays(time()+$unixtime_week,false);
        $this->assertSame("7 days, 0 hours",$timeleft);
        $timeleft = timeleftHoursAndDays(time()+$unixtime_min,true);
        $this->assertSame("1 mins, 0 secs",$timeleft);
        $timeleft = timeleftHoursAndDays(time()+($unixtime_hour*2),false);
        $this->assertSame("2 hours, 0 mins",$timeleft);
        $timeleft = timeleftHoursAndDays(time()+1,false);
        $this->assertSame("0 mins",$timeleft);
        $timeleft = timeleftHoursAndDays(time()-20,false);
        $this->assertSame("Expired",$timeleft);
    }
}
