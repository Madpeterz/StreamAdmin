<?php
abstract class inputFilter_filter_integer extends inputFilter_filter_string
{
    protected function filter_integer(string $value,array $args=[]) : ?int
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(array_key_exists("zeroCheck", $args))
		{
			if($value == "0")
			{
				$this->testOK = FALSE;
				$this->whyfailed = "Zero value detected";
			}
		}
		if(is_numeric($value))
		{
			$testValue = intval($value);
			if(array_key_exists("gtr0", $args))
			{
				if($testValue <= 0)
				{
					$this->testOK = false;
					$this->whyfailed = "Value must be more than zero";
				}
			}
			if($this->testOK) return $testValue;
		}
		else
		{
			$this->whyfailed = "Not a number";
		}
		return null;
	}
}
?>
