<?php

namespace Tests\Control;

use App\Endpoint\Control\Package\Create AS CreatePackage;
use App\Endpoint\Control\Server\Create AS CreateServer;
use App\Endpoint\Control\Stream\Bulkupdate;
use App\Endpoint\Control\Stream\Create AS CreateStream;
use App\Endpoint\Control\Stream\Remove;
use App\Endpoint\Control\Stream\Restore;
use App\Endpoint\Control\Stream\Update;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\Set\StreamSet;
use App\Models\Stream;
use App\Models\Transactions;
use Tests\TestWorker;

class StreamTest extends TestWorker
{
    public function test_RequireFirst()
    {
        // server
        $_POST["domain"] = "test.mypanel.com";
        $_POST["controlPanelURL"] = "https://test.mypanel.com/client";
        $_POST["ipaddress"] = "1.1.1.1";
        $create = new CreateServer();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Server created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");

        // package
        $_POST["name"] = "unittest";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 1234;
        $_POST["days"] = 55;
        $_POST["bitrate"] = 125;
        $_POST["listeners"] = 25;
        $_POST["textureSoldout"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["textureInstockSmall"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["textureInstockSelected"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["enableGroupInvite"] = false;
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["servertypeLink"] = 1;
        $_POST["welcomeNotecardLink"] = 1;
        $_POST["setupNotecardLink"] = 1;
        $create = new CreatePackage();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Package created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $this->resetPost();
    }
    /**
     * @depends test_RequireFirst
     */
    public function test_Create()
    {
        $portnum = 8000;
        $loop = 0;

        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "live";

        while($loop < 10)
        {
            $port = $portnum + (2 * $loop);
            $_POST["port"] = $port;
            $_POST["adminUsername"] = "admin".$loop;
            $_POST["adminPassword"] = substr(sha1("admin".$loop),0,15);
            $_POST["djPassword"] = substr(sha1($_POST["adminPassword"].$loop),0,15);
            $create = new CreateStream();
            $create->process();
            $reply = $create->getOutputObject();
            $this->assertSame("Stream created on port: " . $port, $reply->getSwapTagString("message"), "Message does not appear to be correct");
            $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
            $loop++;
        }
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        $this->resetPost();
        $stream = new Stream();
        $stream->loadId(1);
        $this->assertSame(true, $stream->isLoaded(), "Failed to load stream");
        
        global $system;
        $system->setPage($stream->getStreamUid());

        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "live";
        $_POST["port"] = 9000;
        $_POST["adminUsername"] = "admin0";
        $_POST["adminPassword"] = substr(sha1("admin0"),0,15);
        $_POST["djPassword"] = substr(sha1($_POST["adminPassword"]."0"),0,15);
            
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Stream updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        $this->resetPost();
        $_POST["accept"] = "Accept";
        $stream = new Stream();
        $stream->loadId(1);
        $this->assertSame(true, $stream->isLoaded(), "Failed to load stream");            
        global $system;
        $system->setPage($stream->getStreamUid());

        $Remove = new Remove();
        $Remove->process();
        $reply = $Remove->getOutputObject();
        $this->assertSame("Stream removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }

    /**
     * @depends test_Remove
     */
    public function test_BulkUpdate()
    {
        $this->resetPost();

        $streams = new StreamSet();
        $streams->loadAll();
        $result = $streams->updateFieldInCollection("needWork",true);
        $this->assertSame(true, $result->status, "Failed to update collection");
        $this->assertSame(9, $result->changes, "Incorrect number of entrys updated");

        $expectedUpdateCount = 0;
        while($expectedUpdateCount < 3)
        {
            $this->resetPost();
            $expectedUpdateCount = 0;
            foreach($streams as $stream)
            {
                $accept = "no";
                if(rand(1,2) == 2)
                {
                    $accept = "update";
                    $expectedUpdateCount++;
                }
                $_POST["stream" . $stream->getStreamUid()] = $accept;
                $_POST['stream' . $stream->getStreamUid() . 'adminpw'] = substr(sha1("bulkadmin".$stream->getId()),0,15);
                $_POST['stream' . $stream->getStreamUid() . 'djpw'] = substr(sha1("bulkdj".$stream->getId()),0,15);
            }
        }
        $message = sprintf("%1\$s streams updated",$expectedUpdateCount);
        $Bulkupdate = new Bulkupdate();
        $Bulkupdate->process();
        $reply = $Bulkupdate->getOutputObject();
        $this->assertSame($message, $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }

    /**
     * @depends test_BulkUpdate
     */
    public function test_Restore()
    {
        $this->resetPost();
        $_POST["accept"] = "Accept";
        $stream = new Stream();
        $stream->loadId(2);
        $this->assertSame(true, $stream->isLoaded(), "Failed to load stream");            
        global $system;
        $system->setPage($stream->getStreamUid());

        $region = new Region();
        $region->setName("unittest");
        $createRegionReply = $region->createEntry();
        $this->assertSame(true, $createRegionReply->status, "Failed to create region");

        $reseller = new Reseller();
        $reseller->setAvatarLink(1);
        $reseller->setRate(100);
        $createResellerReply = $reseller->createEntry();
        $this->assertSame(true, $createResellerReply->status, "Failed to create reseller entry");

        $transaction = new Transactions();
        $transaction->setAmount(100);
        $transaction->setAvatarLink(1);
        $transaction->setFromCredits(false);
        $transaction->setPackageLink(1);
        $transaction->setRegionLink($createRegionReply->newId);
        $transaction->setRenew(false);
        $transaction->setResellerLink($createResellerReply->newId);
        $transaction->setSLtransactionUUID("not used");
        $transaction->setTransactionUid("yepper");
        $transaction->setUnixtime(time()-120);
        $transaction->setStreamLink($stream->getId());
        $createTransaction = $transaction->createEntry();
        $this->assertSame("ok", $createTransaction->message, "Failed to create transaction entry");
        $this->assertSame(true, $createTransaction->status, "Failed to create transaction entry");

        $recover = new Restore();
        $recover->process();
        $reply = $recover->getOutputObject();
        $this->assertSame("Stream restored", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");

    }

}

