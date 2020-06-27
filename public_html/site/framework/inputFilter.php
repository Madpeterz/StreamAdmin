<?php
class inputFilter
{
	protected $failure = FALSE;
	protected $testOK = TRUE;
	protected function valueFilter($value=null,string $filter,array $args = array())
	{
		if($value != null)
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
			else if($filter == "array") $value = $this->filter_array($value, $args);
			if($value !== null) return $value;
			$failure = true;
		}
		else
		{
			$this->failure = TRUE;
		}
		if($this->failure)
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
	public function postFilter(string $inputName,string $filter="string",array $args = array(), $default = null)
	{
		$this->failure = FALSE;
		$value = null;
		if(isset($_POST[$inputName]))
		{
			if(in_array($filter,array("array")) == false) $value = stripslashes($_POST[$inputName]);
			else $value = $_POST[$inputName];
			return $this->valueFilter($value, $filter, $args);;
		}
		else
		{
			$this->failure = TRUE;
			return $default;
		}
	}
	public function getFilter(string $inputName,string $filter="string",array $args = array(), $default = null)
	{
		$this->failure = FALSE;
		$value = null;
		if(isset($_GET[$inputName]))
		{
			$value = $_GET[$inputName];
			return $this->valueFilter($value, $filter, $args);
		}
		else
		{
			$this->failure = TRUE;
			return $default;
		}
	}
	protected function filter_array($value) : ?array
	{
		// used by groupped inputs
		if(is_array($value) == true)
		{
			return $value;
		}
		else
		{
			return null;
		}
	}
	protected function filter_string(string $value,array $args=array()) : ?string
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(array_key_exists("minLength", $args))
		{
			if(strlen($value) < $args["minLength"]) $this->testOK = FALSE;
		}
		if(array_key_exists("maxLength", $args))
		{
			if(strlen($value) > $args["maxLength"]) $this->testOK = FALSE;
		}
		if($this->testOK) return $value;
		else return null;
	}
	protected function filter_integer(string $value,array $args=array()) : ?int
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		if(array_key_exists("zeroCheck", $args))
		{
			if($value == "0") $this->testOK = FALSE;
		}
		if(is_numeric($value))
		{
			$testValue = intval($value);
			if(array_key_exists("gtr0", $args))
			{
				if($testValue <= 0) $this->testOK = false;
			}
			if($this->testOK) return $testValue;
		}
		return null;
	}
	protected function filter_float(string $value,array $args=array()) : ?float
    {
        $this->failure = FALSE;
        $this->testOK = TRUE;
        $value = floatval($value);
        if(array_key_exists("zeroCheck", $args))
        {
            if($value == "0") $this->testOK = FALSE;
        }
        if(is_float($value))
        {
            error_log("asdasd");
            if($this->testOK) return floatval($value);
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
		if(strlen($value) == 36) return $value;
		return null;
	}
	protected function filter_vector(string $value,array $args=array())
	{
		$this->failure = FALSE;
		$this->testOK = TRUE;
		$vectorTest = explode(",", str_replace(array("<", " ", ">", "(", ")"), "", $value));
		if(count($vectorTest) == 3)
		{
			if(($this->filter_float($vectorTest[0]) != null) && ($this->filter_float($vectorTest[1]) != null) && ($this->filter_float($vectorTest[2]) != null))
			{
				if(array_key_exists("strict", $args))
				{
					if((substr_count($value, '<') != 1) || (substr_count($value, '>') != 1))
					{
						$this->testOK = FALSE;
					}
				}
			}
			else $this->testOK = FALSE;
		}
		else $this->testOK = FALSE;
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
		$this->failure = FALSE;
		$this->testOK = TRUE;
		$timeTest = explode("/", stl_replace(" ", "", $value));
		if(count($timeTest) == 3)
		{
			if(($timeTest[0] < 1) || ($timeTest[0] > 12)) $this->testOK = FALSE;
			if(($timeTest[1] < 1) || ($timeTest[1] > 31)) $this->testOK = FALSE;
			if(($timeTest[2] < 1970) || ($timeTest[2] > 2999)) $this->testOK = FALSE;
		}
		else $this->testOK = FALSE;
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
			// fails on ALOT of vaild email addresses.
			if(filter_var($value, FILTER_VALIDATE_EMAIL) !== false) return $value;
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
				else $allowed = false;
			}
			else $allowed = false;
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
				if(substr_count('http:', $value) == 1) return $value;
				else $this->testOK = FALSE;
			}
			else if(array_key_exists("isHTTPS", $args))
			{
				if(substr_count('https:', $value) == 1) return $value;
				else $this->testOK = FALSE;
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
        if(($value === true) || (strtolower($value) === "true") || ($value === 1)) return 1;
        return 0;
    }
	protected function filter_json(string $value) : ?string
	{
		$json = json_decode($value, true);
		if(($json === false) || ($json === null)) return null;
		else return $json;
	}
}
?>
