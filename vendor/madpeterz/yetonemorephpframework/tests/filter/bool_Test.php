<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_bool_test extends TestCase
{
    protected inputFilter $testingobject;
    protected function setUp(): void
    {
        $this->testingobject = new inputFilter();
    }
    public function test_bool_notset()
    {
        $results1 = $this->testingobject->getFilter("popcorn", "bool");
        $this->assertSame($results1, null);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_bool_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->testingobject->getFilter("popcorn2", "bool");
        $this->assertSame($results1, null);
        $results1 = $this->testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_bool_set()
    {
        $_GET["popcorn3"] = "true";
        $results1 = $this->testingobject->getFilter("popcorn3", "bool");
        $this->assertSame($results1, true);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn2"] = "0";
        $results1 = $this->testingobject->getFilter("popcorn2", "bool");
        $this->assertSame($results1, false);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn1"] = "yes";
        $results1 = $this->testingobject->getFilter("popcorn1", "bool");
        $this->assertSame($results1, true);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn4"] = 0;
        $results1 = $this->testingobject->getFilter("popcorn4", "bool");
        $this->assertSame($results1, false);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_bool_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->testingobject->getFilter("popcorn4", "bool");
        $this->assertSame($results1, null);
        $results1 = $this->testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
    }
    public function testTrueFalse()
    {
        $_GET["popcorn4"] = "true";
        $results1 = $this->testingobject->getFilter("popcorn4", "truefalse");
        $this->assertSame($results1, 1);
        $_GET["popcorn5"] = "no";
        $results2 = $this->testingobject->getFilter("popcorn5", "truefalse");
        $this->assertSame($results2, 0);
    }

    public function test_bool_via_get_and_post()
    {
        $_GET["popcorn3"] = 1;
        $results1 = $this->testingobject->getBool("popcorn3");
        $this->assertSame($results1, true);

        $_POST["magic3"] = 0;
        $results2 = $this->testingobject->postBool("magic3");
        $this->assertSame($results2, false);
    }
}
