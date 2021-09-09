<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_integer_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_integer_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "No get value found with name: popcorn");
    }
    public function test_integer_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_integer_set()
    {
        $_GET["popcorn2"] = "5";
        $results1 = $this->_testingobject->getFilter("popcorn2", "integer");
        $this->assertSame($results1, 5);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_integer_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->_testingobject->getFilter("popcorn4", "integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "not numeric");
    }
    public function test_integer_zeroChecks()
    {
        $_GET["popcorn5"] = "0";
        $results1 = $this->_testingobject->getFilter("popcorn5", "integer", array("zeroCheck" => true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "rejected: Must not be zero");
        $_GET["popcorn6"] = "22";
        $results1 = $this->_testingobject->getFilter("popcorn6", "integer", array("zeroCheck" => true));
        $this->assertSame($results1, 22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_integer_gtr_zero()
    {
        $_GET["popcorn7"] = "-22";
        $results1 = $this->_testingobject->getFilter("popcorn7", "integer", array("gtr0" => true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "To low ~ Min value: anything higher than zero");
        $_GET["popcorn8"] = "22";
        $results1 = $this->_testingobject->getFilter("popcorn8", "integer", array("gtr0" => true));
        $this->assertSame($results1, 22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }

    public function test_integer_minmax()
    {
        $_GET["popcorn77"] = "55";
        $results1 = $this->_testingobject->getFilter("popcorn77", "integer", array("max" => 30));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "To high ~ Max value: 30");
        $_GET["popcorn78"] = "25";
        $results1 = $this->_testingobject->getFilter("popcorn78", "integer", array("min" => 30));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "To low ~ Min value: 30");
    }

    public function test_integer_via_get_post()
    {
        $_GET["popcorn2"] = "5";
        $results1 = $this->_testingobject->getInteger("popcorn2");
        $this->assertSame($results1, 5);

        $_POST["popcorn3"] = "77";
        $results1 = $this->_testingobject->postInteger("popcorn3");
        $this->assertSame($results1, 77);

        $_GET["popcorn21"] = "5";
        $results1 = $this->_testingobject->getInteger("popcorn21",false,false,null,20);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "To low ~ Min value: 20");
        $this->assertSame($results1, null);

        $_POST["popcorn31"] = "5";
        $results1 = $this->_testingobject->postInteger("popcorn31",false,false,null,20);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "To low ~ Min value: 20");
        $this->assertSame($results1, null);

        $_GET["popcorn41"] = "51";
        $results1 = $this->_testingobject->getInteger("popcorn41",false,false,30);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "To high ~ Max value: 30");
        $this->assertSame($results1, null);

        $_POST["popcorn57"] = "53";
        $results1 = $this->_testingobject->postInteger("popcorn57",false,false,30);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "To high ~ Max value: 30");
        $this->assertSame($results1, null);


        $_POST["popcorn88"] = "53";
        $results1 = $this->_testingobject->postInteger("popcorn88",false,false,100,50);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "");
        $this->assertSame($results1, 53);

        $_POST["popcorn89"] = "77";
        $results1 = $this->_testingobject->postInteger("popcorn89",false,false,100,50);
        $whyfailed = $this->_testingobject->getWhyFailed();
        $this->assertSame($whyfailed, "");
        $this->assertSame($results1, 77);
    }
}
