<?php
abstract class inputFilter_filter_bool extends inputFilter_filter_float
{
    protected function filter_bool(string $value,array $args=array()) : bool
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		return in_array($value,array("true",true,1,"yes","True",TRUE,"TRUE"));
	}
    protected function filter_trueFalse(string $value,array $args=array()) : int
    {
        $value = filter_bool($value);
        if($value === true) return 1;
        return 0;
    }
}
?>
