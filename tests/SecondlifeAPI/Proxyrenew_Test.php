<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\ProxyRenew\Details;
use PHPUnit\Framework\TestCase;

class SecondlifeApiProxyrenew extends TestCase
{
    protected $package = null;
    public function test_Details()
    {
        $this->setupPost("Details");

        $_POST["targetuid"] = "Test Buyer";
        $Details = new Details();
        $this->assertSame("ready",$Details->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Details->getLoadOk(),"Load ok failed");
        $Details->process();
        $this->assertSame("Client account: Test Buyer",$Details->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Details->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(1,$Details->getOutputObject()->getSwapTagInt("dataset_count"),"incorrect number of entrys reported");
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $this->assertSame(3,count($split),"Dataset entry not formated as expected: ".$dataset[0]);
    }

    protected function setupPost(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Proxyrenew/".$target);
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
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
        $raw = time()  . "Proxyrenew".$target. implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}
