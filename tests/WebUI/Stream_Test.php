<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Stream\Bulkupdate;
use App\Endpoint\Control\Stream\Create as StreamCreate;
use App\Endpoint\Control\Stream\Remove as StreamRemove;
use App\Endpoint\Control\Stream\Update;
use App\Endpoint\View\Stream\Bulkupdate as StreamBulkupdate;
use App\Endpoint\View\Stream\Create;
use App\Endpoint\View\Stream\DefaultView;
use App\Endpoint\View\Stream\Inpackage;
use App\Endpoint\View\Stream\Manage;
use App\Endpoint\View\Stream\Needwork;
use App\Endpoint\View\Stream\Onserver;
use App\Endpoint\View\Stream\Ready;
use App\Endpoint\View\Stream\Sold;
use App\Models\Package;
use App\Models\Stream;
use App\Models\Sets\StreamSet;
use Tests\Mytest;

class StreamTest extends Mytest
{
    public function test_Default()
    {
        $StreamPackageSelect = new DefaultView();
        $StreamPackageSelect->process();
        $statuscheck = $StreamPackageSelect->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing streams packages list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
        $this->assertStringContainsString("Ready",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ViewSelectedPackage()
    {
        global $system;
        $package = new Package();
        $status = $package->loadID(1);
        $this->assertSame(true,$status->status,"Unable to load test package");

        $system->setPage($package->getPackageUid());
        $view = new Inpackage();
        $view->process();
        $statuscheck = $view->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing streams list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("UID",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
    }

    /**
     * @depends test_ViewSelectedPackage
     */
    public function test_CreateForm()
    {
        $StreamCreateForm = new Create();
        $StreamCreateForm->process();
        $statuscheck = $StreamCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream create form element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Config",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $streamCreateHandler = new StreamCreate();
        $_POST["port"] = 8006;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["adminUsername"] = "MoreUnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $streamCreateHandler->process();
        $statuscheck = $streamCreateHandler->getOutputObject();
        $this->assertStringContainsString("Stream created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $system;
        $stream = new Stream();
        $status = $stream->loadByField("port",8006);
        $this->assertSame(true,$status->status,"Unable to load test stream");
        $system->setPage($stream->getStreamUid());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream manage form element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Config",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
        $this->assertStringContainsString("8006",$statuscheck,$missing);
        $this->assertStringContainsString("MoreUnitTesting",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $system, $_POST;
        $stream = new Stream();
        $status = $stream->loadByPort(8004);
        $this->assertSame(true,$status->status,"Unable to load test stream");
        $system->setPage($stream->getStreamUid());

        $manageProcess = new Update();
        $_POST["port"] = 8080;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["adminUsername"] = "MoreUnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Stream updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $stream = new Stream();
        $status = $stream->loadByField("port",8080);
        $this->assertSame(true,$status->status,"Unable to load updated stream");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveProcess()
    {
        global $system, $_POST;
        $stream = new Stream();
        $status = $stream->loadByField("port",8080);
        $this->assertSame(true,$status->status,"Unable to load test stream");
        $system->setPage($stream->getStreamUid());

        $removeProcess = new StreamRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Stream removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_RemoveProcess
     */
    public function test_Onserver()
    {
        global $system;
        $system->setPage(1);
        $Onserver = new Onserver();
        $Onserver->process();
        $statuscheck = $Onserver->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream list onserver element";
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
        $this->assertStringContainsString("UnitTestPackage",$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveProcess
     */
    public function test_Ready()
    {
        $Ready = new Ready();
        $Ready->process();
        $statuscheck = $Ready->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream list ready element";
        $this->assertStringContainsString("UID",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveProcess
     */
    public function test_needWork()
    {
        $NeedWork = new Needwork();
        $NeedWork->process();
        $statuscheck = $NeedWork->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream list needwork element";
        $this->assertStringContainsString("UID",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveProcess
     */
    public function test_sold()
    {
        $Sold = new Sold();
        $Sold->process();
        $statuscheck = $Sold->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream list Sold element";
        $this->assertStringContainsString("UID",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Testing",$statuscheck,$missing);
    }

     /**
     * @depends test_sold
     */
    public function test_BulkUpdateForm()
    {
        global $_POST;
        $port_loop = 9000;
        $loop = 1;
        $adminpasswords = [];
        while($loop <= 10)
        {
            $streamCreateHandler = new StreamCreate();
            $_POST["port"] = $port_loop+($loop*2);
            $_POST["packageLink"] = 1;
            $_POST["serverLink"] = 1;
            $_POST["mountpoint"] = "/live";
            $_POST["adminUsername"] = "MoreUnitTesting".$_POST["port"];
            $adp = substr(md5($_POST["port"]."".microtime()."a"),0,8);
            $_POST["adminPassword"] = $adp;
            $adminpasswords[] = $adp;
            $_POST["djPassword"] = substr(md5($_POST["port"]."".microtime()."b"),0,8);
            $_POST["needswork"] = "1";
            $streamCreateHandler->process();
            $statuscheck = $streamCreateHandler->getOutputObject();
            if($statuscheck == false) {
                $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Failed to create test stream");
                break;
            }
            $loop++;
        }

        $Bulkupdate = new StreamBulkupdate();
        $Bulkupdate->process();
        $statuscheck = $Bulkupdate->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream Bulk update element";
        $this->assertStringContainsString("Admin Password",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
        $this->assertStringContainsString("Skip",$statuscheck,$missing);
        $this->assertStringContainsString("Process",$statuscheck,$missing);
        foreach($adminpasswords as $pwd) {
            $this->assertStringContainsString($pwd,$statuscheck,$missing);
        }
    }

    public function test_BulkUpdateProcess()
    {
        global $_POST;
        $whereconfig = [
            "fields" => ["needWork","rentalLink"],
            "values" => [1,null],
            "types" => ["i","i"],
            "matches" => ["=","IS"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        $twitch = 0;
        $updated_counter = 0;
        foreach($stream_set->getAllIds() as $streamid) {
            $stream = $stream_set->getObjectByID($streamid);
            $mode = "update";
            if($twitch == 1) { 
                $mode = "skip";
                $updated_counter++;
            }
            $twitch = !$twitch;
            $_POST["stream" . $stream->getStreamUid()] = $mode;
            if($mode == "update") {
                $_POST["stream" . $stream->getStreamUid() . "adminpw"] = substr(md5($stream->getDjPassword()),0,8);
                $_POST["stream" . $stream->getStreamUid() . "djpw"] = substr(md5($stream->getDjPassword()),0,8);
            }
        }
        $updated_counter = $stream_set->getCount() - $updated_counter;
        $StreamBulkupdate = new Bulkupdate();
        $_POST["accept"] = "Accept";
        $StreamBulkupdate->process();
        $statuscheck = $StreamBulkupdate->getOutputObject();
        $this->assertStringContainsString($updated_counter." streams updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

    }


}
