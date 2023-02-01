<?php

namespace StreamAdminR7;

use App\Endpoint\View\Reports\BreakdownMonth;
use App\Endpoint\View\Reports\BreakdownYear;
use App\Endpoint\View\Reports\ComapreYears;
use App\Endpoint\View\Reports\DefaultView;
use App\Helpers\AvatarHelper;
use App\Helpers\RegionHelper;
use App\Helpers\ResellerHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use PHPUnit\Framework\TestCase;

class ReportsTest extends TestCase
{
    public function test_Toolbox()
    {
        global $testsystem;
        $avatarhelper = new AvatarHelper();
        $status = $avatarhelper->loadOrCreate("2f9c3e36-6fb3-40c5-92f9-0c6a5d230f66","TransactionTest Avatar");
        $this->assertSame(true,$status,"Unable to find a avatar to use");
        $avatar = $avatarhelper->getAvatar();
        $resellerhelper = new ResellerHelper();
        $status = $resellerhelper->loadOrCreate(1,true,40);
        $this->assertSame(true,$status,"Unable to find a reseller to use");
        $reseller = $resellerhelper->getReseller();
        $stream = new Stream();
        $status = $stream->loadID(1);
        $this->assertSame(true,$status->status,"Unable to find a stream to use");
        $server = new Server();
        $status = $server->loadID($stream->getServerLink());
        $this->assertSame(true,$status->status,"Unable to find a server to use");
        $package = new Package();
        $status = $package->loadID(1);
        $this->assertSame(true,$status->status,"Unable to find a package to use");
        $regionHelper = new RegionHelper();
        $status = $regionHelper->loadOrCreate("Unittest");
        $this->assertSame(true,$status,"Unable to find a region to use");
        $region = $regionHelper->getRegion();
        $TransactionsHelper = new TransactionsHelper();
        $startunixtime = mktime(12,12,12,02,04,2019);
        $loop = 10;
        while($loop > 0) {
            $amount = 3000 - (5*$loop);
            $renewal = (time() + $loop) % 3;
            $flag = false;
            if($renewal < 1) { 
                $flag = true;
            }
            $status = $TransactionsHelper->createTransaction($avatar,$package,$stream,$reseller,$region,$amount,$flag,$startunixtime+($testsystem->unixtimeDay()*$loop));
            if($status != true) {
                $this->assertSame(true,$status,"Error creating a test transaction");
                break;
            }
            $loop--;
        }
        $startunixtime = mktime(12,12,12,02,04,2021);
        $loop = 10;
        while($loop > 0) {
            $amount = 5000 - (5*$loop);
            $renewal = (time() + $loop) % 3;
            $flag = false;
            if($renewal < 1) { 
                $flag = true;
            }
            $status = $TransactionsHelper->createTransaction($avatar,$package,$stream,$reseller,$region,$amount,$flag,$startunixtime+($testsystem->unixtimeDay()*$loop));
            if($status != true) {
                $this->assertSame(true,$status,"Error creating a test transaction");
                break;
            }
            $loop--;
        }

        $config = new DefaultView();
        $config->process();
        $statuscheck = $config->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing reports page element";
        $this->assertStringContainsString("Toolbox",$statuscheck,$missing);
        $this->assertStringContainsString("Month breakdown",$statuscheck,$missing);
        $this->assertStringContainsString("Year breakdown",$statuscheck,$missing);
        $this->assertStringContainsString("Year vs Year",$statuscheck,$missing);
        $this->assertStringContainsString("This week",$statuscheck,$missing);
        $this->assertStringContainsString("L$ total [New]",$statuscheck,$missing);
    }

    /**
     * @depends test_Toolbox
     */
    public function test_MonthBreakdown()
    {
        global $_GET;
        $_GET["month"] = 4;
        $_GET["year"] = 2019;
        $monthBreakdown = new BreakdownMonth();
        $monthBreakdown->process();
        $statuscheck = $monthBreakdown->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing BreakdownMonth reports page element";
        $this->assertStringContainsString("Fast report",$statuscheck,$missing);
        $this->assertStringContainsString("New",$statuscheck,$missing);
        $this->assertStringContainsString("Renews",$statuscheck,$missing);
        $this->assertStringContainsString("L$ total [Rewew]",$statuscheck,$missing);
        $this->assertStringContainsString("Count / New",$statuscheck,$missing);
        $this->assertStringContainsString("Change from last week",$statuscheck,$missing);
    }

    /**
     * @depends test_Toolbox
     */
    public function test_YearBreakdown()
    {
        global $_GET;
        $_GET["year"] = 2019;
        $monthBreakdown = new BreakdownYear();
        $monthBreakdown->process();
        $statuscheck = $monthBreakdown->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing BreakdownYear reports page element";
        $this->assertStringContainsString("Fast report",$statuscheck,$missing);
        $this->assertStringContainsString("New",$statuscheck,$missing);
        $this->assertStringContainsString("Renews",$statuscheck,$missing);
        $this->assertStringContainsString("L$ total [Rewew]",$statuscheck,$missing);
        $this->assertStringContainsString("Count / New",$statuscheck,$missing);
        $this->assertStringContainsString("Change from last month",$statuscheck,$missing);
    }

    /**
     * @depends test_Toolbox
     */
    public function test_YearCompare()
    {
        global $_GET;
        $_GET["yeara"] = 2019;
        $_GET["yearb"] = 2021;
        $monthBreakdown = new ComapreYears();
        $monthBreakdown->process();
        $statuscheck = $monthBreakdown->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing ComapreYears reports page element";
        $this->assertStringContainsString("2019",$statuscheck,$missing);
        $this->assertStringContainsString("2021",$statuscheck,$missing);
        $this->assertStringContainsString("Transactions",$statuscheck,$missing);
        $this->assertStringContainsString("L$ total",$statuscheck,$missing);
        $this->assertStringContainsString("Change L$",$statuscheck,$missing);
        $this->assertStringContainsString("Change Transactions",$statuscheck,$missing);
        $this->assertStringContainsString("Total",$statuscheck,$missing);
        $this->assertStringContainsString("May",$statuscheck,$missing);
    }




}