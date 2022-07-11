<?php

namespace StreamAdminR7;

use App\Models\Slconfig;
use App\Switchboard\Sys;
use PHPUnit\Framework\TestCase;

class Website_Test extends TestCase
{
    public function test_WebsiteView()
    {
        global $_SERVER, $_POST;
        foreach(array_keys($_POST) as $a) {
            unset($_POST[$a]);
        }
        foreach(array_keys($_GET) as $a) {
            unset($_GET[$a]);
        }
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['REQUEST_URI'] = "objects";
        include "src/public_html/index.php";
        
        $statuscheck = $this->getActualOutputForAssertion();
        $this->assertStringContainsString("Owner",$statuscheck,"missing from output");
        $this->assertStringContainsString("Last seen",$statuscheck,"Missing from output");
    }

    /**
     * @depends test_WebsiteView
     */
    public function test_WebsiteControl()
    {
        global $_SERVER, $_POST;
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_SERVER['REQUEST_URI'] = "objects/clear";
        $_POST["accept"] = "Accept";

        include "src/public_html/index.php";
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertStringContainsString("Objects cleared from DB",$json_obj["message"]);
        $this->assertSame(true,$json_obj->status,"Status check failed");
    }
}