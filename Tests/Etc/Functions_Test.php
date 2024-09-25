<?php

namespace StreamAdminR7;

use Tests\Mytest;
use YAPF\Framework\Helpers\FunctionHelper;

class FunctionHelperPublic extends FunctionHelper
{
    public function sha256(string $input): string
    {
        return parent::sha256($input);
    }

    public function userAgentIdToName(int $agentId): string
    {
        return parent::userAgentIdToName($agentId);
    }

    public function timeDisplay(int $secs): string
    {
        return parent::timeDisplay($secs);
    }

    public function expiredAgo(
        $unixtime = 0,
        bool $withSeconds = false,
        string $expiredWord = "Expired",
        string $activeWord = "Active"
    ): string {
        return parent::expiredAgo($unixtime,$withSeconds,$expiredWord,$activeWord);
    }
    /**
     * get_opts
     * @return mixed[]
     */
    public function getOpts(): array
    {
        return parent::getOpts();
    }

    public function timeRemainingHumanReadable(
        $unixtime = 0,
        bool $withSeconds = false,
        string $expiredWord = "Expired"
    ): string {
        return parent::timeRemainingHumanReadable($unixtime, $withSeconds, $expiredWord);
    }

    public function isChecked(bool $input_value): string
    {
        return parent::isChecked($input_value);
    }
}

class FunctionsTest extends Mytest
{
    public function test_expiredAgo()
    {
        $helper = new FunctionHelperPublic();
        $expiredAgo = $helper->expiredAgo(time()-20,true);
        $this->assertSame("20 secs",$expiredAgo);
        $expiredAgo = $helper->expiredAgo(time()-120,false);
        $this->assertSame("2 mins",$expiredAgo);
        $expiredAgo = $helper->expiredAgo(time()+120,false);
        $this->assertSame("Active",$expiredAgo);
    }

    public function test_is_checked()
    {
        $helper = new FunctionHelperPublic();
        $this->assertSame("",$helper->isChecked(false));
        $this->assertSame(" checked ",$helper->isChecked(true));
    }

    public function test_timeleftHoursAndDays()
    {
        global $system;
        $helper = new FunctionHelperPublic();
        $timeleft =  $helper->timeRemainingHumanReadable(time()+$system->unixtimeWeek(),false);
        $this->assertSame("7 days, 0 hours",$timeleft);
        $timeleft = $helper->timeRemainingHumanReadable(time()+$system->unixtimeMin(),true);
        $this->assertSame("1 mins, 0 secs",$timeleft);
        $timeleft = $helper->timeRemainingHumanReadable(time()+($system->unixtimeHour()*2),false);
        $this->assertSame("2 hours, 0 mins",$timeleft);
        $timeleft = $helper->timeRemainingHumanReadable(time()+1,false);
        $this->assertSame("0 mins",$timeleft);
        $timeleft = $helper->timeRemainingHumanReadable(time()-20,false);
        $this->assertSame("Expired",$timeleft);
    }
}
