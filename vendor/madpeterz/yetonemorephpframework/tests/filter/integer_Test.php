<?php
use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_integer_test extends TestCase
{
    protected $_testingobject;
    public function test_integer_notset()
    {
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn","integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "No get value found with name: popcorn");
    }
    public function test_integer_empty()
    {
        $_GET["popcorn2"] = "";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn2","integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_integer_set()
    {
        $_GET["popcorn3"] = "5";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn3","integer");
        $this->assertSame($results1, 5);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_integer_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4","integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"InputFilter can not deal with objects you crazy person");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->_testingobject->getFilter("popcorn4","integer");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"Expects value to be numeric but its not");
    }
    public function test_integer_zeroChecks()
    {
        $_GET["popcorn5"] = "0";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn5","integer",array("zeroCheck"=>true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"Zero value detected");
        $_GET["popcorn6"] = "22";
        $results1 = $this->_testingobject->getFilter("popcorn6","integer",array("zeroCheck"=>true));
        $this->assertSame($results1, 22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"");
    }
    public function test_integer_gtr_zero()
    {
        $this->_testingobject = new inputFilter();
        $_GET["popcorn7"] = "-22";
        $results1 = $this->_testingobject->getFilter("popcorn7","integer",array("gtr0"=>true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"Value must be more than zero");
        $_GET["popcorn8"] = "22";
        $results1 = $this->_testingobject->getFilter("popcorn8","integer",array("gtr0"=>true));
        $this->assertSame($results1, 22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"");
    }
}
?>
