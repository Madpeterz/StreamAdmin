<?php
abstract class inputFilter_filter_array extends inputFilter_base
{
    protected function filter_array($value,array $args=[]) : ?array
	{
		// used by groupped inputs
		if(is_array($value) == true)
		{
			return $value;
		}
		else
		{
			$this->whyfailed = "not an array";
			return null;
		}
	}
}
?>
