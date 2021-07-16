<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_array_test extends TestCase
{
    protected inputFilter $testingobject;
    protected function setUp(): void
    {
        $this->testingobject = new inputFilter();
    }
    public function test_array_notset()
    {
        $results1 = $this->testingobject->postFilter("popcorn", "array");
        $this->assertSame($results1, null);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "No post value found with name: popcorn");
    }
    public function test_array_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->testingobject->getFilter("popcorn2", "array");
        $this->assertSame($results1, null);
        $results1 = $this->testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_array_set()
    {
        $_GET["popcorn3"] = ["yes","no"];
        $results1 = $this->testingobject->getFilter("popcorn3", "array");
        $this->assertSame($results1, ["yes","no"]);
        $results2 = $this->testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_array_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->testingobject->getFilter("popcorn4", "array");
        $this->assertSame($results1, null);
        $results1 = $this->testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->testingobject->getFilter("popcorn4", "array");
        $this->assertSame($results1, null);
        $results1 = $this->testingobject->getWhyFailed();
        $this->assertSame($results1, "Not an array");
    }
    public function test_array_via_get_and_post()
    {
        $_GET["popcorn3"] = ["yes","no"];
        $results1 = $this->testingobject->getArray("popcorn3");
        $this->assertSame($results1, ["yes","no"]);

        $_POST["magic3"] = ["up","down","left"];
        $results2 = $this->testingobject->postArray("magic3");
        $this->assertSame($results2, ["up","down","left"]);
    }
}
