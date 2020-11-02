<?php
use PHPUnit\Framework\TestCase;
use YAPF\InputFilter\InputFilter as inputFilter;

class inputFilter_float_test extends TestCase
{
    protected $_testingobject;
    public function test_float_notset()
    {
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn","float");
        $this->assertSame($results1, null);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "No get value found with name: popcorn");
    }
    public function test_float_empty()
    {
        $_GET["popcorn2"] = "";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn2","float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1, "");
    }
    public function test_float_set()
    {
        $_GET["popcorn3"] = "5.24";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn3", "float");
        $this->assertSame($results1, 5.24);
        $results2 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results2, "");
    }
    public function test_float_invaild()
    {
        $_GET["popcorn4"] = new inputFilter();
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn4","float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"InputFilter can not deal with objects you crazy person");
        $_GET["popcorn4"] = "ten";
        $results1 = $this->_testingobject->getFilter("popcorn4","float");
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"Expects value to be numeric but its not");
    }
    public function test_float_zeroChecks()
    {
        $_GET["popcorn5"] = "0.00";
        $this->_testingobject = new inputFilter();
        $results1 = $this->_testingobject->getFilter("popcorn5","float",array("zeroCheck"=>true));
        $this->assertSame($results1, null);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"Zero value detected");
        $_GET["popcorn6"] = "22.22";
        $results1 = $this->_testingobject->getFilter("popcorn6","float",array("zeroCheck"=>true));
        $this->assertSame($results1, 22.22);
        $results1 = $this->_testingobject->getWhyFailed();
        $this->assertSame($results1,"");
    }
}
?>
