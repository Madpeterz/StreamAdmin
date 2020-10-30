<?php
abstract class inputFilter_filter_vector extends inputFilter_filter_date
{
    protected function filter_vector(string $value,array $args=[])
	{
		$this->failure = FALSE;
		$this->testOK = FALSE;
		$vectorTest = explode(",", str_replace(array("<", " ", ">", "(", ")"), "", $value));
		if(count($vectorTest) == 3)
		{
			if(($this->filter_float($vectorTest[0]) != null) && ($this->filter_float($vectorTest[1]) != null) && ($this->filter_float($vectorTest[2]) != null))
			{
				if(array_key_exists("strict", $args))
				{
					if((substr_count($value, '<') != 1) || (substr_count($value, '>') != 1))
					{
						$this->whyfailed = "Strict mode: Required <  & > Missing";
					}
					else
					{
						$this->testOK = TRUE;
					}
				}
				else
				{
					$this->testOK = TRUE;
				}
			}
			else
			{
				$this->whyfailed = "the 3 Parts required to be floats or integers. example: 42.4,33,415.11";
			}
		}
		else
		{
			$this->whyfailed = "Require 3 parts split with , example: 23,42,55";
		}
		if($this->testOK)
		{
			if(function_exists("llString2Vector"))
			{
				if(array_key_exists("convert", $args)) return llString2Vector($vectorTest[0], $vectorTest[1], $vectorTest[2]);
				else return $value;
			}
			else
			{
				return $value;
			}
		}
		return null;
	}
}
?>
