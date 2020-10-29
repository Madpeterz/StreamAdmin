<?php
abstract class inputFilter_filter_string extends inputFilter_filter_array
{
    protected function filter_string(string $value,array $args=array()) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if((array_key_exists("maxLength", $args) == true) && (array_key_exists("minLength", $args)  == true))
		{
			if($args["minLength"] > $args["maxLength"])
			{
				$this->whyfailed = "Length values are mixed up";
				$this->testOK = FALSE;
			}
		}
		if($this->testOK)
		{
			if(array_key_exists("minLength", $args) == true)
			{
				if(strlen($value) < $args["minLength"])
				{
					$this->whyfailed = "Failed min length check";
					$this->testOK = FALSE;
				}
			}
			if(array_key_exists("maxLength", $args) == true)
			{
				if(strlen($value) > $args["maxLength"])
				{
					$this->whyfailed = "Failed max length check";
					$this->testOK = FALSE;
				}
			}
		}
		if($this->testOK)
		{
			return $value;
		}
		return null;
	}
}
?>
