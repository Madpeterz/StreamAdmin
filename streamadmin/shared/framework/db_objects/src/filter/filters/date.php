<?php
abstract class inputFilter_filter_date extends inputFilter_filter_email
{
    protected function filter_date(string $value,array $args=array())
	{
		// Expected format MM/DD/YYYY
		$this->failure = FALSE;
		$this->testOK = TRUE;
		$timeTest = explode("/", stl_replace(" ", "", $value));
		if(count($timeTest) == 3)
		{
			if(($timeTest[0] < 1) || ($timeTest[0] > 12))
			{
				$this->whyfailed = "Month out of range";
				$this->testOK = FALSE;
			}
			else if(($timeTest[1] < 1) || ($timeTest[1] > 31))
			{
				$this->whyfailed = "Day out of range";
				$this->testOK = FALSE;
			}
			else if(($timeTest[2] < 1970) || ($timeTest[2] > 2999))
			{
				$this->whyfailed = "Year out of range";
				$this->testOK = FALSE;
			}
		}
		else
		{
			$this->testOK = FALSE;
			$this->whyfailed = "Year out of range";
		}
		if($this->testOK)
		{
			$unix = strtotime(implode('/', $timeTest));
			if(array_key_exists("asUNIX", $args)) return $unix;
			if(array_key_exists("humanReadable", $args)) return date('l jS \of F Y', $unix);
			return $value;
		}
		return null;
	}
}
?>
