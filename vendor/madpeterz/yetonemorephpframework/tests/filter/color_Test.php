<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_color_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_color_notset_hex()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_color_empty_hex()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_color_set_hex()
    {
        $_GET["popcorn3"] = "#FFBBCC";
        $results1 = $this->_testingobject->getFilter("popcorn3", "color", ["isHEX" => true]);
        $this->assertSame($results1, "#FFBBCC");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn4"] = "442211";
        $results1 = $this->_testingobject->getFilter("popcorn4", "color", ["isHEX" => true]);
        $this->assertSame($results1, "442211");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_color_invaild_hex()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn5"] = "GGHHGG";
        $results1 = $this->_testingobject->getFilter("popcorn5", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "value did not match any IsHex rules");
        $_GET["popcorn6"] = 4;
        $results1 = $this->_testingobject->getFilter("popcorn6", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "value did not match any IsHex rules");
        $_GET["popcorn7"] = "99";
        $results1 = $this->_testingobject->getFilter("popcorn7", "color", ["isHEX" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "value did not match any IsHex rules");
    }

    public function test_color_notset_lsl()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "color");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_color_empty_lsl()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "color");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_color_set_lsl()
    {
        $_GET["popcorn3"] = "<1,0.2,0.6>";
        $results1 = $this->_testingobject->getFilter("popcorn3", "color");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $this->assertSame($results1, "<1,0.2,0.6>");
        $_GET["popcorn4"] = "0.4,0.3,0.88";
        $results1 = $this->_testingobject->getFilter("popcorn4", "color");
        $this->assertSame($results1, "0.4,0.3,0.88");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_color_invaild_lsl()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "color");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn5"] = "<5,6,7>";
        $results1 = $this->_testingobject->getFilter("popcorn5", "color");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "one or more values are out of spec");
        $_GET["popcorn6"] = 4;
        $results1 = $this->_testingobject->getFilter("popcorn6", "color");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
        $_GET["popcorn7"] = "99";
        $results1 = $this->_testingobject->getFilter("popcorn7", "color");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
    }

    public function test_color_notset_rgb()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_color_empty_rgb()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_color_set_rgb()
    {
        $_GET["popcorn3"] = "<1,0.2,0.6>";
        $results1 = $this->_testingobject->getFilter("popcorn3", "color", ["isRGB" => true]);
        $this->assertSame($results1, "<1,0.2,0.6>");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn4"] = "212,34,55";
        $results1 = $this->_testingobject->getFilter("popcorn4", "color", ["isRGB" => true]);
        $this->assertSame($results1, "212,34,55");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_color_invaild_rgb()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn5"] = "<-44,444,0.1>";
        $results1 = $this->_testingobject->getFilter("popcorn5", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "one or more values are out of spec");
        $_GET["popcorn6"] = 4;
        $results1 = $this->_testingobject->getFilter("popcorn6", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
        $_GET["popcorn7"] = "99";
        $results1 = $this->_testingobject->getFilter("popcorn7", "color", ["isRGB" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
    }

    public function test_color_via_get_and_post()
    {
        $_GET["popcorn3"] = "<1,0.2,0.6>";
        $results1 = $this->_testingobject->getColour("popcorn3",false,true);
        $results2 = $this->_testingobject->getColor("popcorn3",false,true);
        $this->assertSame($results1, "<1,0.2,0.6>");
        $this->assertSame($results2, "<1,0.2,0.6>");
        $_POST["popcorn4"] = "<212,34,55>";
        $results3 = $this->_testingobject->postColour("popcorn4",false,true);
        $results4 = $this->_testingobject->postColor("popcorn4",false,true);
        $this->assertSame("<212,34,55>", $results3);
        $this->assertSame("<212,34,55>",$results4);
    }
}
