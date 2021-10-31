<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_vector_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_vector_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "vector");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_vector_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "vector");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_vector_set()
    {
        $_GET["vectorwith"] = "<1,2,4>";
        $results1 = $this->_testingobject->getFilter("vectorwith", "vector");
        $this->assertSame($results1, "<1,2,4>");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["vectorwithout"] = "5,7,8";
        $results1 = $this->_testingobject->getFilter("vectorwithout", "vector");
        $this->assertSame($results1, "5,7,8");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_vector_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "vector");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "is a object");
        $_GET["popcorn4"] = "you+boxnotenabledgmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "vector");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
    }
    public function testVectorSetStrictMode()
    {
        $_GET["vectorwith"] = "<1,2,4>";
        $results1 = $this->_testingobject->getFilter("vectorwith", "vector", ["strict" => true]);
        $this->assertSame($results1, "<1,2,4>");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["vectorwithout"] = "5,7,8";
        $results1 = $this->_testingobject->getFilter("vectorwithout", "vector", ["strict" => true]);
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Did not match any vaild Vector patterns");
    }

    public function test_vector_via_get_post()
    {
        $_GET["vector1"] = "<1,2,4>";
        $results1 = $this->_testingobject->getVector("vector1");
        $this->assertSame($results1, "<1,2,4>");

        $_POST["vector2"] = "<4,5,6>";
        $results2 = $this->_testingobject->postVector("vector2");
        $this->assertSame($results2, "<4,5,6>");
    }
}
