<?php

namespace Tests\Control;

use App\Endpoint\Control\Stream\Create AS CreateStream;
use App\Endpoint\Control\Package\Create AS CreatePackage;
use App\Endpoint\Control\Server\Create AS CreateServer;
use App\Endpoint\Control\Transactions\Remove;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\Stream;
use App\Models\Transactions;
use Tests\TestWorker;

class TransactionsTest extends TestWorker
{
    public function test_PreRemove()
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

        $portnum = 8000;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "live";
        $port = $portnum;
        $_POST["port"] = $port;
        $_POST["adminUsername"] = "admin0";
        $_POST["adminPassword"] = substr(sha1("admin0"),0,15);
        $_POST["djPassword"] = substr(sha1($_POST["adminPassword"]."0"),0,15);
        $create = new CreateStream();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Stream created on port: " . $port, $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");

        $stream = new Stream();
        $stream->loadId(1);
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
    }
    /**
     * @depends test_PreRemove
     */
    public function test_Remove()
    {
        global $system;
        $_POST["accept"] = "Accept";
        $system->setPage("yepper");
        $remove = New Remove();
        $remove->process();
        $reply = $remove->getOutputObject();
        $this->assertSame("Transaction removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}