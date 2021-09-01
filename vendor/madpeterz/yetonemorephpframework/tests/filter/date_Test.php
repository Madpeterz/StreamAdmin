<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_date_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_date_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "date");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_date_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_date_set()
    {
        $_GET["popcorn3"] = "11/02/1972";
        $results1 = $this->_testingobject->getFilter("popcorn3", "date");
        $this->assertSame($results1, "11/02/1972");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_date_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "44/02/1908";
        $results1 = $this->_testingobject->getFilter("popcorn4", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Month out of range");
        $_GET["popcorn4"] = "01/41/1908";
        $results1 = $this->_testingobject->getFilter("popcorn4", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Day out of range");
        $_GET["popcorn4"] = "01/21/1908";
        $results1 = $this->_testingobject->getFilter("popcorn4", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Year out of range");
    }

    public function testBadFormat()
    {
        $_GET["popcorn4"] = "01/21";
        $results1 = $this->_testingobject->getFilter("popcorn4", "date");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Bad formating");
    }

    public function testDateSetAsUnix()
    {
        $_GET["popcorn3"] = "11/02/1972";
        $results1 = $this->_testingobject->getFilter("popcorn3", "date", ["asUNIX" => true]);
        $this->assertSame($results1, '66614400');
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }

    public function testDateSetAsHuman()
    {
        $_GET["popcorn3"] = "11/02/1972";
        $results1 = $this->_testingobject->getFilter("popcorn3", "date", ["humanReadable" => true]);
        $this->assertSame($results1, "Thursday 2nd of November 1972");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }

    public function test_date_via_get_post()
    {
        $_GET["popcorn3"] = "11/02/1972";
        $results1 = $this->_testingobject->getDate("popcorn3",true);
        $this->assertSame($results1, '66614400');

        $_POST["popcorn4"] = "11/02/1972";
        $results2 = $this->_testingobject->postDate("popcorn4");
        $this->assertSame($results2, "11/02/1972");
    }
}
