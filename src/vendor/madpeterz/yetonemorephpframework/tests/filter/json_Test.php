<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_json_test extends TestCase
{
    protected $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_json_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "json");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_json_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "json");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_json_set()
    {
        $test_string = '{"menu": {"id": "file","value": "File","popup": {"menuitem": ';
        $test_string .= '[{"value": "New", "onclick": "CreateNewDoc()"},{"value": "Open",';
        $test_string .= '"onclick": "OpenDoc()"}, {"value": ';
        $test_string .= '"Close", "onclick": "CloseDoc()"}]}}}';
        $_GET["popcorn3"] = $test_string;
        $results1 = $this->_testingobject->getFilter("popcorn3", "json");
        $this->assertSame($results1, json_decode($test_string, true));
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_json_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "json");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "you+boxnotenabledgmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "json");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Not a vaild json object string");
    }
}
