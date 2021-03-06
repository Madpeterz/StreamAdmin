<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_string_test extends TestCase
{
    protected $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_string_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "No get value found with name: popcorn");
    }
    public function test_string_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_string_set()
    {
        $_GET["popcorn3"] = "ready";
        $results1 = $this->_testingobject->getFilter("popcorn3");
        $this->assertSame($results1, "ready");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
        $direct = $this->_testingobject->varFilter("Magic", "");
        $this->assertSame($direct, "Magic");
    }
    public function test_string_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
    }
    public function test_string_minlength()
    {
        $_GET["popcorn5"] = "toshort";
        $_GET["popcorn6"] = "vaildlength";
        $results1 = $this->_testingobject->getFilter("popcorn5", "string", array("minLength" => 30));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Failed min length check");
        $results1 = $this->_testingobject->getFilter("popcorn6", "string", array("minLength" => 5));
        $this->assertSame($results1, "vaildlength");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_string_maxlength()
    {
        $_GET["popcorn7"] = "tolong";
        $_GET["popcorn8"] = "ok";
        $results1 = $this->_testingobject->getFilter("popcorn7", "string", array("maxLength" => 3));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Failed max length check");
        $results1 = $this->_testingobject->getFilter("popcorn8", "string", array("maxLength" => 33));
        $this->assertSame($results1, "ok");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_string_rangedlength()
    {
        $_GET["popcorn9"] = "ng";
        $_GET["popcorn10"] = "badbad";
        $_GET["popcorn11"] = "popcorn";
        $_GET["popcorn12"] = "pass";
        $results1 = $this->_testingobject->getFilter("popcorn9", "string", array("minLength" => 3,"maxLength" => 10));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Failed min length check");
        $results1 = $this->_testingobject->getFilter("popcorn10", "string", array("minLength" => 3,"maxLength" => 4));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Failed max length check");
        $results1 = $this->_testingobject->getFilter("popcorn11", "string", array("minLength" => 30,"maxLength" => 4));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Length values are mixed up");
        $results1 = $this->_testingobject->getFilter("popcorn12", "string", array("minLength" => 2,"maxLength" => 5));
        $this->assertSame($results1, "pass");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
}
