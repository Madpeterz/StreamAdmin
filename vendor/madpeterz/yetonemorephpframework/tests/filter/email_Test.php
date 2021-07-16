<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_email_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_email_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "email");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_email_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "email");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_email_set()
    {
        $_GET["popcorn3"] = "you@email.com";
        $results1 = $this->_testingobject->getFilter("popcorn3", "email");
        $this->assertSame($results1, "you@email.com");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn3"] = "you+boxenabled@email.com";
        $results1 = $this->_testingobject->getFilter("popcorn3", "email");
        $this->assertSame($results1, "you+boxenabled@email.com");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn5"] = "you@email.com";
        $results1 = $this->_testingobject->getFilter("popcorn5", "email", ["no_mailboxs"]);
        $this->assertSame($results1, "you@email.com");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $direct = $this->_testingobject->varFilter("you@email.com", "email");
        $this->assertSame($direct, "you@email.com");
    }
    public function test_email_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "email");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "you+boxnotenabled@gmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "email", ["no_mailboxs"]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "no_mailboxs");
        $_GET["popcorn4"] = "you+boxnotenabledgmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "email");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "Required @ missing");
        $_GET["popcorn4"] = "youboxnotenabledgmail@com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "email");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "Failed vaildation after removing mailbox");
        $this->assertSame($results1, null);
    }

    public function test_email_via_get_post()
    {
        $_GET["popcorn3"] = "you@email.com";
        $results1 = $this->_testingobject->getEmail("popcorn3");
        $this->assertSame($results1, "you@email.com");

        $_POST["popcorn5"] = "magic@email.com";
        $results2 = $this->_testingobject->postEmail("popcorn5",true);
        $this->assertSame($results2, "magic@email.com");
    }
}
