<?php
class inputFilter extends error_logging
{
	protected $failure = FALSE;
	protected $testOK = TRUE;
	protected $whyfailed = "";

	public function get_why_failed()
	{
		return $this->whyfailed;
	}

	public function postFilter(string $inputName,string $filter="string",array $args = array(), $default = null)
	{
		$this->failure = FALSE;
		$this->whyfailed = "";
		$value = null;
		if(isset($_POST[$inputName]))
		{
			if(in_array($filter,array("array")) == false) $value = stripslashes($_POST[$inputName]);
			else $value = $_POST[$inputName];
			if($this->whyfailed != "") $this->addError(__FILE__,__FUNCTION__,$this->whyfailed);
			return $this->valueFilter($value, $filter, $args);
		}
		else
		{
			$this->failure = TRUE;
			$this->whyfailed = "No post value found with name: ".$inputName."";
			return $default;
		}
	}
	public function getFilter(string $inputName,string $filter="string",array $args = array(), $default = null)
	{
		$this->failure = FALSE;
		$this->whyfailed = "";
		$value = null;
		if(isset($_GET[$inputName]))
		{
			$value = $_GET[$inputName];
			if($this->whyfailed != "") $this->addError(__FILE__,__FUNCTION__,$this->whyfailed);
			return $this->valueFilter($value, $filter, $args);
		}
		else
		{
			$this->failure = TRUE;
			$this->whyfailed = "No get value found with name: ".$inputName."";
			return $default;
		}
	}

	protected function valueFilter($value=null,string $filter,array $args = array())
	{
		$this->failure = false;
		if($value != null)
		{
			if(is_string($value) == true)
			{
				$this->testOK = TRUE;
				if($filter == "string") $value = $this->filter_string($value, $args);
				else if($filter == "integer") $value = $this->filter_integer($value, $args);
				else if(($filter == "double") || ($filter == "float")) $value = $this->filter_float($value, $args);
				else if($filter == "checkbox") $value = $this->filter_checkbox($value, $args);
				else if($filter == "bool") $value = $this->filter_bool($value, $args);
				else if(($filter == "uuid") || ($filter == "key")) $value = $this->filter_uuid($value, $args);
				else if($filter == "vector") $value = $this->filter_vector($value, $args);
				else if($filter == "date") $value = $this->filter_date($value, $args);
				else if($filter == "email") $value = $this->filter_email($value, $args);
				else if($filter == "url") $value = $this->filter_url($value, $args);
				else if($filter == "color") $value = $this->filter_color($value, $args);
	            else if($filter == "trueFalse") $value = $this->filter_trueFalse($value);
				else if($filter == "json") $value = $this->filter_json($value);
				if($value !== null) return $value;
				$this->failure = TRUE;
			}
			else
			{
				if($filter == "array")
				{
					return $this->filter_array($value, $args);
				}
				else
				{
					$value = null;
					$this->whyfailed = "Type error expected a string but got somthing else";
				}
			}
		}
		else
		{
			$this->failure = TRUE;
		}
		if($this->failure == true)
		{
			if($filter == "checkbox") return 0;
            else if($filter == "trueFalse") return 0;
		}
		return null;
	}
	/*
	Filters a $_POST value into a known stable state.

	$inputName: the name of the post value
	$filter (default: string):
		string,integer,double|float,checkbox,bool,uuid|key,vector,date,email,url,color,trueFalse,json
	*/
	protected function filter_array($value) : ?array
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
	protected function filter_integer(string $value,array $args=array()) : ?int
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
	protected function filter_float(string $value,array $args=array()) : ?double
	{
		return $this->filter_double($value,$args);
	}
	protected function filter_double(string $value,array $args=array()) : ?double
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
	protected function filter_checkbox(string $value,array $args=array())
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(array_key_exists("asString", $args))
		{
			$testValue = $this->filter_string($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else if((array_key_exists("asDouble", $args)) || (array_key_exists("asFloat", $args)))
		{
			$testValue = $this->filter_float($value, $args);
			if($testValue !== null) return $testValue;
			else return 0;
		}
		else if(array_key_exists("asBool", $args))
		{
			$testValue = $this->filter_bool($value, $args);
			if($testValue !== null) return $testValue;
			else return false;
		}
		else if((array_key_exists("asUUID", $args)) || (array_key_exists("asKey", $args)))
		{
			$testValue = $this->filter_uuid($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else if(array_key_exists("asVector", $args))
		{
			$testValue = $this->filter_vector($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else if(array_key_exists("asDate", $args))
		{
			$testValue = $this->filter_date($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else if(array_key_exists("asEmail", $args))
		{
			$testValue = $this->filter_email($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else if(array_key_exists("asURL", $args))
		{
			$testValue = $this->filter_url($value, $args);
			if($testValue !== null) return $testValue;
			else return "";
		}
		else
		{
			$testValue = $this->filter_integer($value, $args);
			if($testValue !== null) return $testValue;
			else return 0;
		}
		return null;
	}
	protected function filter_bool(string $value,array $args=array()) : bool
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		return in_array($value,array("true",true,1,"yes","True",TRUE,"TRUE"));
	}
	protected function filter_uuid(string $value,array $args=array()) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(strlen($value) == 36)
		{
			$m = 0;
			// 6a58369e-5b9d-0062-8de1-f0d841b8cbf0
			// [a-f0-9]{8}\-[a-f0-9]{4}\--[a-f0-9]{4}\--[a-f0-9]{4}\-
			if(preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $value, $m) == true)
			{
				return $value;
			}
			else
			{
				$this->whyfailed = "Not a vaild UUID4";
			}
		}
		else
		{
			$this->whyfailed = "Incorrect uuid length";
		}
		return null;
	}
	protected function filter_vector(string $value,array $args=array())
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
	protected function filter_email(string $value,array $args=array()) : ?string
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
				else
				{
					$this->whyfailed = "Requires HTTP protocall but failed that check.";
					$this->testOK = FALSE;
				}
			}
			else if(array_key_exists("isHTTPS", $args))
			{
				if(substr_count('https:', $value) == 1)
				{
					return $value;
				}
				else
				{
					$this->whyfailed = "Requires HTTPS protocall but failed that check.";
					$this->testOK = FALSE;
				}
			}
            else return $value;
		}
		return null;
	}
	protected function filter_color(string $value,array $args=array()) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if((function_exists("rgb2hex") == true) && (function_exists("hex2rgb") == true))
		{
			if(array_key_exists("convert", $args))
			{
				if(array_key_exists("hex", $args))
				{
					if($this->postFilter($value, "color", "isLSL") != null)
					{
						$vectorTest = explode(",", str_replace(array("<", " ", ">"), "", $value));
						$vectorTest[0] *= 255;
						$vectorTest[1] *= 255;
						$vectorTest[2] *= 255;
						return rgb2hex($vectorTest);
					}
					if($this->postFilter($value, "color", "isRGB") != null)
					{
						$vectorTest = explode(",", str_replace(array("<", " ", ">"), "", $value));
						return rgb2hex($vectorTest);
					}
					else return $value;
				}
				if(array_key_exists("lsl", $args))
				{
					if($this->postFilter($value, "color", "isHEX") != null)
					{
						$rgb = hex2rgb($value);
						$rgb[0] /= 255;
						$rgb[1] /= 255;
						$rgb[2] /= 255;
						return "<".implode(',', $rgb).">";
					}
					if($this->postFilter($value, "color", "isRGB") != null)
					{
						$rgb = explode(",", str_replace(array("<", " ", ">"), "", $value));
						$rgb[0] /= 255;
						$rgb[1] /= 255;
						$rgb[2] /= 255;
						return "<".implode(',', $rgb).">";
					}
				}
				if(array_key_exists("rgb", $args))
				{
					if($this->postFilter($value, "color", "isHEX") != null)
					{
						return hex2rgb($value);
					}
					if($this->postFilter($value, "color", "isLSL") != null)
					{
						$lsl = explode(",", str_replace(array("<", " ", ">"), "", $value));
						$lsl[0] *= 255;
						$lsl[1] *= 255;
						$lsl[2] *= 255;
						return implode(',', $lsl);
					}
				}
			}
		}
		if(array_key_exists("isHEX", $args))
		{
			if(preg_match('/^#[a-f0-9]{6}$/i', $color)) return $value;
			else if(preg_match('/^[a-f0-9]{6}$/i', $color)) return $value;
		}
		if(array_key_exists("isLSL", $args))
		{
			$testLSL = $this->filter_vector($value);
			if($testLSL == null) $this->testOK = FALSE;
			else
			{
				$vectorTest = explode(",", str_replace(array("<", " ", ">"), "", $testLSL));
				if(($vectorTest[0] < 0) || ($vectorTest[0] > 1) || ($vectorTest[1] < 0) || ($vectorTest[1] > 1) || ($vectorTest[2] < 0) || ($vectorTest[2] > 1)) $this->testOK = FALSE;
				else return $value;
			}
		}
		if(array_key_exists("isRGB", $args))
		{
			$testRGB = $this->filter_vector($value);
			if($testRGB == null) $this->testOK = FALSE;
			else
			{
				$vectorTest = explode(",", str_replace(array("<", " ", ">"), "", $testLSL));
				if(($vectorTest[0] < 0) || ($vectorTest[0] > 255) || ($vectorTest[1] < 0) || ($vectorTest[1] > 255) || ($vectorTest[2] < 0) || ($vectorTest[2] > 255)) $this->testOK = FALSE;
				else return $value;
			}
		}
		if($this->testOK) return $value;
		else return null;
	}
    protected function filter_trueFalse(string $value) : int
    {
        if(($value === true) || (strtolower($value) === "true") || ($value === 1))
		{
			return 1;
		}
		else if(($value === false) || (strtolower($value) === "false") || ($value === 0))
		{
			return 0;
		}
		else
		{
			$this->whyfailed = "Unable to check state value";
			return null;
		}
    }
	protected function filter_json(string $value) : ?string
	{
		$json = json_decode($value, true);
		if(($json === false) || ($json === null)) return null;
		else return $json;
	}
}
?>
