<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_url_test extends TestCase
{
    protected ?inputFilter $_testingobject;
    protected function setUp(): void
    {
        $this->_testingobject = new inputFilter();
    }
    public function test_url_notset()
    {
        $results1 = $this->_testingobject->getFilter("popcorn", "url");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_url_empty()
    {
        $_GET["popcorn2"] = "";
        $results1 = $this->_testingobject->getFilter("popcorn2", "url");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is empty");
    }
    public function test_url_set()
    {
        $_GET["popcorn3"] = "http://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn3", "url");
        $this->assertSame($results1, "http://google.com");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
        $_GET["popcorn3"] = "https://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn3", "url");
        $this->assertSame($results1, "https://google.com");
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_url_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4", "url");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "is a object");
        $_GET["popcorn4"] = "you+boxnotenabled@gmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "url");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
        $_GET["popcorn4"] = "you+boxnotenabledgmail.com";
        $results1 = $this->_testingobject->getFilter("popcorn4", "url");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }

    public function test_url_args_http()
    {
        $_GET["popcorn6"] = "http://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn6", "url", ["isHTTP" => true]);
        $this->assertSame($results1, "http://google.com");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");

        $_GET["popcorn7"] = "https://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn7", "url", ["isHTTP" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "http:// is missing from the value!");

        $_GET["popcorn7"] = "googlehttp://what.com";
        $results1 = $this->_testingobject->getFilter("popcorn7", "url", ["isHTTP" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "http:// is missing from the start of the value!");
    }

    public function test_url_args_https()
    {
        $_GET["popcorn6"] = "https://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn6", "url", ["isHTTPS" => true]);
        $this->assertSame($results1, "https://google.com");
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");

        $_GET["popcorn7"] = "http://google.com";
        $results1 = $this->_testingobject->getFilter("popcorn7", "url", ["isHTTPS" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "https:// is missing from the value!");

        $_GET["popcorn7"] = "googlehttps://what.com";
        $results1 = $this->_testingobject->getFilter("popcorn7", "url", ["isHTTPS" => true]);
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "https:// is missing from the start of the value!");
    }

    public function test_url_via_get_post()
    {
        $_GET["popcorn3"] = "https://google.com";
        $results1 = $this->_testingobject->getUrl("popcorn3");
        $this->assertSame($results1, "https://google.com");

        $_POST["popcorn4"] = "https://reddit.com";
        $results2 = $this->_testingobject->postUrl("popcorn4");
        $this->assertSame($results2, "https://reddit.com");
    }
}
