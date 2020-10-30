<?php
abstract class inputFilter_filter_email extends inputFilter_filter_http
{
    protected function filter_email(string $value,array $args=[]) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(in_array("no_mailboxs",$args) == true)
		{
			// fails on ALOT of vaild email addresses. but much faster
			if(filter_var($value, FILTER_VALIDATE_EMAIL) !== false)
			{
				return $value;
			}
			else
			{
				$this->whyfailed = "Does not appeat to be a vaild EMAIL (No mailboxs supported)";
			}
			return null;
		}
		else
		{
			$allowed = true;
			$local_value = "";
			$mailbox_value = "";
			$domain_value = "";
			$bits = explode("@",$value);
			if(count($bits) == 2)
			{
				$domain_value = $bits[1];
				$mailbox = explode("+",$bits[0]);
				$local_value = $mailbox[0];
				if(count($mailbox) == 2)
				{
					$mailbox_value = $mailbox[1];
				}
				$filter_testvalue = "".$local_value."@".$domain_value."";
				if(filter_var($filter_testvalue, FILTER_VALIDATE_EMAIL) !== false)
				{
					if($mailbox_value != "") $value = "".$local_value."+".$mailbox_value."@".$domain_value."";
				}
				else
				{
					$this->whyfailed = "Failed vaildation after removing mailbox";
					$allowed = false;
				}
			}
			else
			{
				$this->whyfailed = "Required @ missing";
				$allowed = false;
			}
			if($allowed == true) return $value;
			return null;
		}
	}
}
?>
