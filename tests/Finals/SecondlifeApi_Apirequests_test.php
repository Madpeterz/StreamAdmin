<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Apirequests\Next;
use App\R7\Model\Apirequests;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\R7\Set\ApirequestsSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApi_Apirequests extends TestCase
{
    protected ?Server $server = null;
    protected $events = [
        "Eventcleardjs",
        "Eventdisablerevoke",
        "Eventresetpasswordrevoke",
        "Eventrevokeresetusername",
        "Optautodjnext",
        "Optpasswordreset",
        "Opttoggleautodj"
    ];
    public function setUp(): void
    {
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
    }
    public function test_ConfigService(): void
    {
        $this->server->setApiLink(5);
        $update = $this->server->updateEntry();
        if($update["message"] != "No changes made")
        {
            $this->assertSame("ok",$update["message"],"Unable to update server settings");
            $this->assertSame(true,$update["status"],"Unable to update server settings");
        }
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->server->setApiLink(2);
        $this->server->setApiURL("http://127.0.0.1/fake/centova.php");
        $this->server->setApiPassword("fake");
        $this->server->setApiServerStatus(true);
        $this->server->setApiSyncAccounts(true);
        $this->server->setOptPasswordReset(true);
        $this->server->setOptAutodjNext(true);
        $this->server->setOptToggleAutodj(true);
        $this->server->setOptToggleStatus(true);
        $this->server->setEventEnableStart(true);
        $this->server->setEventDisableExpire(true);
        $this->server->setEventDisableRevoke(true);
        $this->server->setEventRevokeResetUsername(true);
        $this->server->setEventResetPasswordRevoke(true);
        $this->server->setEventClearDjs(true);
        $this->server->setEventRecreateRevoke(true);
        $this->server->setEventCreateStream(true);
        $this->server->seteventStartSyncUsername(true);
        $update = $this->server->updateEntry();
        $this->assertSame("ok",$update["message"],"Invaild message state");
        $this->assertSame(true,$update["status"],"Unable to update server settings");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_Create_Apirequests()
    {

        $stream = new Stream();
        $stream->setServerLink($this->server->getId());
        $stream->setPort("7552");
        $stream->setPackageLink(1);
        $stream->setNeedWork(0);
        $stream->setOriginalAdminUsername("magic");
        $stream->setAdminUsername("magic2");
        $stream->setAdminPassword("oasswiid");
        $stream->setDjPassword("djpasshereK");
        $stream->setMountpoint("/test");
        $stream->setStreamUid("fake");
        $status = $stream->createEntry();
        $this->assertSame("ok",$status["message"],"Incorrect message");
        $this->assertSame(true,$status["status"],"Unable to create stream");
        $all_created = true;
        foreach($this->events as $event)
        {
            $apiR = new Apirequests();
            $apiR->setServerLink($this->server->getId());
            $apiR->setStreamLink($stream->getId());
            $apiR->setLastAttempt(time());
            $apiR->setMessage("Not processed");
            $apiR->setEventname($event);
            $status = $apiR->createEntry();
            if($status["status"] == false) {
                $all_created = false;
                break;
            }
        }
        $this->assertSame(true,$all_created,"Failed to create pending API events");
        global $sql;
        $sql->sqlSave();
    }

    /**
     * @depends test_Create_Apirequests
    */
    public function test_Process_Apirequests()
    {
        global $sql, $_POST, $slconfig;
        $_POST["method"] = "Mailserver";
        $_POST["action"] = "Next";
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
            "method",
            "action",
            "mode",
            "objectuuid",
            "regionname",
            "ownerkey",
            "ownername",
            "pos",
            "objectname",
            "objecttype",
        ];
        $real = [];
        foreach($storage as $valuename)
        {
            $real[] = $_POST[$valuename];
        }
        $_POST["unixtime"] = time();
        $raw = time()  . implode("",$real) . $slconfig->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $count = $sql->basicCountV2("apirequests");
        $accepted_messages = [
            "passed" => true,
            "none" => true,
            "API call optAutodjNext failed with: No avatar setup" => false,
            "ok" => true,
        ];
        while($count["count"] > 0) {
            $nextApiAction = new Next();
            $this->assertSame("Not processed",$nextApiAction->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
            $this->assertSame(true,$nextApiAction->getLoadOk(),"Load ok failed");
            $nextApiAction->process();
            $message = $nextApiAction->getOutputObject()->getSwapTagString("message");
            $this->assertSame(true,array_key_exists($message,$accepted_messages),$message." is not supported");
            $this->assertSame($accepted_messages[$message],$nextApiAction->getOutputObject()->getSwapTagBool("status"),"incorrect status");
            $count = $sql->basicCountV2("apirequests");
        }
    }

}