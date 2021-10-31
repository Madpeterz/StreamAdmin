<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_checkbox_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_checkbox_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "checkbox");
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "No get value found with name: popcorn");
    }
    public function test_checkbox_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "checkbox");
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_checkbox_set()
    {
        $_GET["popcorn2"] = "5";
        $results1 = $this->_testingobject->getFilter("popcorn2", "checkbox");
        $this->assertSame($results1, 5);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_checkbox_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "checkbox");
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->_testingobject->getFilter("popcorn4", "checkbox");
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "not numeric");
    }

    public function test_checkbox_notset_as_string()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "checkbox", ["filter" => "string"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "No get value found with name: popcorn");
    }
    public function test_checkbox_empty_as_string()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "checkbox", ["filter" => "string"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_checkbox_set_as_string()
    {
        $_GET["popcorn2"] = "magic";
        $results1 = $this->_testingobject->getFilter("popcorn2", "checkbox", ["filter" => "string"]);
        $this->assertSame($results1, "magic");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_checkbox_invaild_as_string()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "checkbox", ["filter" => "string"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn5"] = [];
        $results1 = $this->_testingobject->getFilter("popcorn5", "checkbox", ["filter" => "string"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is an array but running test: checkbox");
    }

    public function test_checkbox_feedback_loop()
    {
        $_GET["popcorn4"] = 44;
        $results1 = $this->_testingobject->getFilter("popcorn4", "checkbox", ["filter" => "checkbox"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "filter self loop detected");
    }

    public function test_checkbox_unknown_filter()
    {
        $_GET["popcorn4"] = 44;
        $results1 = $this->_testingobject->getFilter("popcorn4", "checkbox", ["filter" => "magic"]);
        $this->assertSame($results1, 0);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Unknown filter: magic");
    }

    public function test_checkbox_via_get_and_post()
    {
        $_GET["popcorn3"] = 5;
        $results1 = $this->_testingobject->getCheckbox("popcorn3");
        $this->assertSame(5, $results1);

        $_POST["magic3"] = [54,22];
        $results2 = $this->_testingobject->postCheckbox("magic3","array");
        $this->assertSame([54,22], $results2);
    }
}