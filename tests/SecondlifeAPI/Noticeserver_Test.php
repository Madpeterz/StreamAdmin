<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Noticeserver\Next;
use App\Endpoint\Secondlifeapi\Noticeserver\UpdateNotecards;
use App\Endpoint\Secondlifeapi\Renew\Details;
use App\Models\Rental;
use App\Models\Sets\NoticenotecardSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiNoticeserver extends TestCase
{
    protected $package = null;
    public function test_Next()
    {
        $this->setupPost("Next");

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $rental = new Rental();
        $this->assertSame(true,$rental->loadByRentalUid($split[0])->status,"Unable to load rental to adjust timeleft");
        $rental->setExpireUnixtime(time()-120);
        $this->assertSame(true,$rental->updateEntry()->status,"Unable to update rental");

        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }

    /**
     * @depends test_Next
     */
    public function test_UpdateNotecards()
    {
        $this->setupPost("UpdateNotecards");

        $_POST["notecards"] = "Unittest1,Unittest2,Magic3,Wolf4,Lineofsight5";
        $UpdateNotecards = new UpdateNotecards();
        $this->assertSame("ready",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$UpdateNotecards->getLoadOk(),"Load ok failed");
        $UpdateNotecards->process();
        $this->assertSame("ok",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$UpdateNotecards->getOutputObject()->getSwapTagBool("status"),"marked as failed");

        $noticenotecardset = new NoticenotecardSet();
        $this->assertSame(true,$noticenotecardset->loadAll()->status,"Unable to load notecard set");
        $this->assertSame(6,$noticenotecardset->getCount(),"Incorrect number of static notecards found");

        $_POST["notecards"] = "Magic3,Wolf4,Lineofsight5";
        $UpdateNotecards = new UpdateNotecards();
        $this->assertSame("ready",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$UpdateNotecards->getLoadOk(),"Load ok failed");
        $UpdateNotecards->process();
        $this->assertSame("ok",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$UpdateNotecards->getOutputObject()->getSwapTagBool("status"),"marked as failed");



        $noticenotecardset = new NoticenotecardSet();
        $this->assertSame(true,$noticenotecardset->loadAll()->status,"Unable to load notecard set");
        $this->assertSame(6,$noticenotecardset->getCount(),"Incorrect number of static notecards found");
        $missing_count = 0;
        foreach($noticenotecardset->getAllIds() as $id)
        {
            $noticenotecard = $noticenotecardset->getObjectByID($id);
            if($noticenotecard->getMissing() == true)
            {
                $missing_count++;
            }
        }
        $this->assertSame(2,$missing_count,"Incorrect number of notecards marked as missing");

        $_POST["notecards"] = "none";
        $UpdateNotecards = new UpdateNotecards();
        $this->assertSame("ready",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$UpdateNotecards->getLoadOk(),"Load ok failed");
        $UpdateNotecards->process();
        $this->assertSame("ok - purged: 2 notecards",$UpdateNotecards->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$UpdateNotecards->getOutputObject()->getSwapTagBool("status"),"marked as failed");

        $noticenotecardset = new NoticenotecardSet();
        $this->assertSame(true,$noticenotecardset->loadAll()->status,"Unable to load notecard set");
        $this->assertSame(4,$noticenotecardset->getCount(),"Incorrect number of static notecards found");
        $missing_count = 0;
        foreach($noticenotecardset->getAllIds() as $id)
        {
            $noticenotecard = $noticenotecardset->getObjectByID($id);
            if($noticenotecard->getMissing() == true)
            {
                $missing_count++;
            }
        }
        $this->assertSame(3,$missing_count,"Incorrect number of notecards marked as missing");
    }

    protected function setupPost(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Noticeserver/".$target);
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
$_POST["version"] = "2.0.0.0";

$storage = [
            "version",
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
        $raw = time()  . "Noticeserver".$target.implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}
