<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_uuid_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_uuid_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "uuid");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_uuid_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "uuid");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_uuid_set()
    {
        $_GET["uuidv1"] = "62cfb6ea-1f3e-11eb-adc1-0242ac120002";
        $results1 = $this->_testingobject->getFilter("uuidv1", "uuid");
        $this->assertSame($results1, "62cfb6ea-1f3e-11eb-adc1-0242ac120002");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["uuidv4"] = "b3c9fcc8-1d62-4ad2-9f20-a78bb1bb20a6";
        $results1 = $this->_testingobject->getFilter("uuidv4", "uuid");
        $this->assertSame($results1, "b3c9fcc8-1d62-4ad2-9f20-a78bb1bb20a6");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_uuid_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "uuid");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "is a object");
        $_GET["popcorn4"] = "you+boxnotenabledgmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "uuid");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Not a vaild v1 or v4 uuid");
    }

    public function test_uuid_via_get_post()
    {
        $_GET["Auuidv1"] = "62cfb6ea-1f3e-11eb-adc1-0242ac120002";
        $results1 = $this->_testingobject->getUUID("Auuidv1");
        $this->assertSame($results1, "62cfb6ea-1f3e-11eb-adc1-0242ac120002");

        $_POST["Buuidv1"] = "11cfb6ea-1f3e-11eb-adc1-0242ac120002";
        $results1 = $this->_testingobject->postUUID("Buuidv1");
        $this->assertSame($results1, "11cfb6ea-1f3e-11eb-adc1-0242ac120002");
    }
}
