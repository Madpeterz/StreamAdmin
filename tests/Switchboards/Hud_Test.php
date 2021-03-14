<?php

namespace StreamAdminR7;

use App\Switchboard\Hud;
use App\Endpoint\SecondLifeApi\Renew\Details;
use App\R7\Model\Slconfig;
use PHPUnit\Framework\TestCase;

class HudTest extends TestCase
{
    public function test_Hud()
    {
        $this->SetupPost("Costs");
        $this->setupRentalUid();
        new Hud();
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"Status missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("old_expire_time",$json_obj),"old_expire_time missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("cost",$json_obj),"cost missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("systemowner",$json_obj),"systemowner missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("ok",$json_obj["message"],"incorrect reply");
        $this->assertSame("1",$json_obj["status"],"marked as failed");
        $this->assertGreaterThan(time(),$json_obj["old_expire_time"],"marked as expired");
        $this->assertGreaterThan(0,$json_obj["cost"],"cost value not set");
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$json_obj["systemowner"],"incorrect owner uuid returned");
    }

    public function test_HudViaPublichtml()
    {
        $this->SetupPost("Costs");
        $this->setupRentalUid();
        include "src/public_html/hud.php";
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"Status missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("old_expire_time",$json_obj),"old_expire_time missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("cost",$json_obj),"cost missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("systemowner",$json_obj),"systemowner missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("ok",$json_obj["message"],"incorrect reply");
        $this->assertSame("1",$json_obj["status"],"marked as failed");
        $this->assertGreaterThan(time(),$json_obj["old_expire_time"],"marked as expired");
        $this->assertGreaterThan(0,$json_obj["cost"],"cost value not set");
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$json_obj["systemowner"],"incorrect owner uuid returned");
    }

    protected function setupRentalUid()
    {
        global $_POST;
        $_POST["avatarUUID"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $_POST["rentalUid"] = $split[0];
    }

    protected function SetupPost($action)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Rentals";
        $_POST["action"] = $action;
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
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
        $raw = time()  . implode("",$real) . $slconfig->getHudLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}