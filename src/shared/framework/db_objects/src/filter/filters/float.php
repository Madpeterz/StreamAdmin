<?php
abstract class inputFilter_filter_float extends inputFilter_filter_integer
{
    protected function filter_float(string $value,array $args=[]) : ?double
	{
		return $this->filter_double($value,$args);
	}
	protected function filter_double(string $value,array $args=[]) : ?double
    {
        $this->failure = FALSE;
        $this->testOK = TRUE;
        if(is_double($value))
        {
			$value = doubleval($value);
	        if(array_key_exists("zeroCheck", $args))
	        {
				if($value == "0")
				{
					$this->testOK = FALSE;
					$this->whyfailed = "Zero value detected";
				}
	        }
            if($this->testOK) return $value;
        }
		else
		{
			$this->whyfailed = "Not a double";
		}
        return null;
    }
}
?>
