<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Details\Send;
use App\Endpoint\SecondLifeApi\Renew\Details;
use PHPUnit\Framework\TestCase;

class SecondlifeApiDetails extends TestCase
{
    public function test_Resend()
    {
        global $_POST, $system;
        $system->forceProcessURI("Details/Resend");
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
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
        $raw = time()  . "DetailsResend".implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);


        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $_POST["rentalUid"] = $split[0];

        $resend = new Send();
        $this->assertSame("ready",$resend->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$resend->getLoadOk(),"Load ok failed");
        $resend->process();
        $this->assertSame("Details request accepted, it should be with you shortly!",$resend->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$resend->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }
}
