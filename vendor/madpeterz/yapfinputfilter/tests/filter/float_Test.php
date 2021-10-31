<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_float_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_float_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "float");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_float_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_float_set()
    {
        $_GET["popcorn3"] = "5.24";
        $results1 = $this->_testingobject->getFilter("popcorn3", "float");
        $this->assertSame($results1, 5.24);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_float_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->_testingobject->getFilter("popcorn4", "float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "not numeric");
    }
    public function test_float_zeroChecks()
    {
        $_GET["popcorn5"] = "0.00";
        $results1 = $this->_testingobject->getFilter("popcorn5", "float", array("zeroCheck" => true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Zero value detected");
        $_GET["popcorn6"] = "22.22";
        $results1 = $this->_testingobject->getFilter("popcorn6", "float", array("zeroCheck" => true));
        $this->assertSame($results1, 22.22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }

    public function test_float_via_get_post()
    {
        $_GET["popcorn3"] = "5.24";
        $results1 = $this->_testingobject->getFloat("popcorn3");
        $this->assertSame($results1, 5.24);

        $_POST["popcorn4"] = "11.44";
        $results1 = $this->_testingobject->postFloat("popcorn4");
        $this->assertSame($results1, 11.44);
    }
}
