<?php

namespace YAPF\InputFilter;

use Exception;

abstract class Rules extends Checks
{
    public function htmlDecode(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $this->valueAsString = html_entity_decode($this->valueAsString);
        return $this;
    }


    /**
     * supports: string,int,float or array as the check value
     */
    public function isNot($value): InputFilter
    {
        if (strval($this->valueAsString) === strval($value)) {
            $this->failed("Values match");
        }
        if ((is_double($value) == true) || (is_float($value) == true)) {
            $A = intval(($this->valueAsFloat * 100)) / 100;
            $B = intval(($value * 100)) / 100;
            if ($A == $B) {
                $this->failed("Floats match");
            }
            return $this;
        }
        if (is_array($value) == false) {
            return $this;
        }
        if (count($value) != count($this->valueAsArray)) {
            return $this;
        }
        if ($this->valueAsArray === null) {
            $this->failed("Array and value are both null");
            return $this;
        }
        if (count(array_diff($value, $this->valueAsArray)) == 0) {
            $this->failed("Arrays match");
            return $this;
        }
        return $this;
    }

    /**
     * isJson
     * Checks if the string can be converted into a json object
     * if so the array value is updated to the json object.
     */
    public function isJson(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        try {
            $raw = html_entity_decode($this->valueAsString);
            $json = json_decode($raw, true);
            if (($json === false) || ($json === null)) {
                $this->failed("Failed to unpack json");
            }
            $this->valueAsArray = $json;
        } catch (Exception $e) {
            $this->failed("Error unpacking json");
        }
        return $this;
    }
    public function isUuid(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $uuid_specs = [
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-4[0-9A-Fa-f]{3}\-[89ABab][0-9A-Fa-f]{3}\-[0-9A-Fa-f]{12}$/i',
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}$/i',
        ];
        $hadMatch = false;
        foreach ($uuid_specs as $spec) {
            if (preg_match($spec, $this->valueAsString) == false) {
                continue;
            }
            $hadMatch = true;
            break;
        }
        if ($hadMatch == false) {
            $this->failed("Did not match any UUID specs on file");
        }
        return $this;
    }
    public function isUrl(bool $httpsOnly = false): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $bits = explode(".", $this->valueAsString);
        if (count($bits) < 2) {
            $this->failed("expected a TLD but none given");
            return $this;
        }
        $match = "http";
        if ($httpsOnly == true) {
            $match .= "s";
        }
        if (filter_var($this->valueAsString, FILTER_VALIDATE_URL) === false) {
            $this->failed("Not a vaild URL");
        }
        $this->checkStartsWith($match);
        return $this;
    }
    public function isHexColor(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $hex_specs = [
            '/^#[a-f0-9]{6}$/i',
            '/^[a-f0-9]{6}$/i',
            '/^#[a-f0-9]{3}$/i',
            '/^[a-f0-9]{3}$/i',
        ];
        $hadMatch = false;
        foreach ($hex_specs as $spec) {
            if (preg_match($spec, $this->valueAsString) == false) {
                continue;
            }
            $hadMatch = true;
            break;
        }
        if ($hadMatch == false) {
            $this->failed("Failed hex color checks");
        }
        return $this;
    }

    public function isRgbVector3(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        return $this->isVector3(true, 0, 255);
    }

    public function isEmail(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        if (filter_var($this->valueAsString, FILTER_VALIDATE_EMAIL) === false) {
            $this->failed("Not a vaild email address");
        }
        return $this;
    }

    public function isVector3(bool $strict = true, ?float $valuesMin = null, ?float $valuesMax = null): InputFilter
    {
        if ($strict == true) {
            $this->valueAsString = html_entity_decode($this->valueAsString);
            $this->checkStartsWith("<");
            $this->checkEndsWith(">");
        }
        if ($this->valueAsString == null) {
            return $this;
        }

        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $this->valueAsString));
        if (count($vectorTest) != 3) {
            $this->failed("Vector3 was not made of x,y,z as expected but had " . count($vectorTest) . " entrys!");
            return $this;
        }
        if (($valuesMin === null) || ($valuesMax === null)) {
            return $this;
        }
        $step = ["x","y","z"];
        $loop = 0;
        foreach ($vectorTest as $bit) {
            if (($bit < $valuesMin) || ($bit > $valuesMax)) {
                $this->failed($step[$loop] . " is out of bounds: " . $valuesMin . " to " . $valuesMax);
                break;
            }
            $loop++;
        }
        return $this;
    }

    /**
     * isDate
     * checks if the string is a date formated "MM/DD/YYYY",
     * if is matchs then:
     * int value is updated to unixtime
     * HumanReadable value is updated
     */
    public function isDate(): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $timeTest = explode("/", str_replace(" ", "", $this->valueAsString));
        if (count($timeTest) != 3) {
            $this->failed("Not formated with 3 bits");
            return $this;
        }
        $monthValue = intval($timeTest[0]);
        $dayValue = intval($timeTest[1]);
        $yearValue = intval($timeTest[2]);

        if (($monthValue < 1) || ($monthValue > 12)) {
            $this->failed("Month out of range");
            return $this;
        } elseif (($dayValue < 1) || ($dayValue > 31)) {
            $this->failed("Day out of range");
            return $this;
        } elseif (($yearValue < 1970) || ($yearValue > 2999)) {
            $this->failed("Year out of range");
            return $this;
        }

        $date = new \DateTime(
            $yearValue . "-" .  $monthValue . "-" . $dayValue,
            new \DateTimeZone('Europe/London')
        );
        $this->valueAsInt = $date->format("U");
        $this->valueAsFloat = $this->valueAsInt;
        $timezone = date_default_timezone_get();
        date_default_timezone_set('Europe/London');
        $this->valueAsHumanReadable = date('l jS \of F Y', $this->valueAsInt);
        date_default_timezone_set($timezone);

        return $this;
    }
}
