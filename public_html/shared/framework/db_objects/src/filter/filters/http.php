<?php
abstract class inputFilter_filter_http extends inputFilter_filter_json
{
    protected function filter_url(string $value,array $args=array()) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(filter_var($value, FILTER_VALIDATE_URL) !== false)
		{
			if(array_key_exists("isHTTP", $args))
			{
				if(substr_count('http:', $value) == 1)
				{
					return $value;
				}
				$this->whyfailed = "Requires HTTP protocall but failed that check.";
				$this->testOK = FALSE;
			}
			else if(array_key_exists("isHTTPS", $args))
			{
				if(substr_count('https:', $value) == 1)
				{
					return $value;
				}
				$this->whyfailed = "Requires HTTPS protocall but failed that check.";
				$this->testOK = FALSE;
			}
            return $value;
		}
		return null;
	}
}
?>
