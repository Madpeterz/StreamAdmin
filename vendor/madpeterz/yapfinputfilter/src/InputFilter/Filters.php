<?php

namespace YAPF\InputFilter;

abstract class Filters extends Base
{
    /**
     * filterArray
     * does a quick check if the input is an array.
     * @return mixed[] or null
     */
    protected function filterArray($value, array $args = []): ?array
    {
        $this->filterIsString = false;
        return $value; // :) tests are done before we get to me
    }
    /**
     * filterBool
     * Checks if the value is in the array
     * if not returns false.
     */
    protected function filterBool(string $value, array $args = []): bool
    {
        $this->filterIsString = false;
        $this->failure = false;
        $this->testOK = true;
        return in_array($value, ["1","true",true,1,"yes","True","TRUE"], true);
    }
    /**
     * filterTrueFalse
     * uses FilterBool but converts to 1 or 0
     */
    protected function filterTruefalse(string $value, array $args = []): int
    {
        $this->filterIsString = false;
        $invalue = $value;
        $value = $this->filterBool($value);
        if ($value === true) {
            return 1;
        }
        return 0;
    }
    /**
     * filterCheckbox
     * filters as integer by default
     * but if filter is set in the args
     * can filter by any other filter type.
     * @return mixed or null
     */
    protected function filterCheckbox(string $value, array $args = [])
    {
        $filter_as = "integer";
        $this->failure = true;
        $this->testOK = false;
        if (is_array($args) == true) {
            if (count($args) > 0) {
                if (array_key_exists("filter", $args) == true) {
                    $filter_as = $args["filter"];
                }
            }
        }
        if ($filter_as != "checkbox") {
            return $this->varFilter($value, $filter_as);
        }
        $this->whyfailed = "filter self loop detected";
        return null;
    }

    protected function colorSupportIsHex(string $value): ?string
    {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            return $value;
        } elseif (preg_match('/^[a-f0-9]{6}$/i', $value)) {
            return $value;
        }
        $this->whyfailed = "value did not match any IsHex rules";
        return null;
    }

    protected function colorSupportLSLVector(string $value, float $maxvalue = 1): ?string
    {
        $testLSL = $this->filterVector($value);
        if ($testLSL == null) {
            return null;
        }
        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $testLSL));
        $tests = [];
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[0]); // R
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[1]); // G
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[2]); // B
        if (in_array(false, $tests) == true) {
            $this->whyfailed = "one or more values are out of spec";
            return null;
        }
        return $value;
    }

    /**
     * filterColor
     * Does stuff not sure what blame shado.
     * @return mixed or mixed[] or null
     */
    protected function filterColor(string $value, array $args = [])
    {
        // default is LSL, supply a isXXX rule to switch
        if (array_key_exists("isHEX", $args)) {
            $value = $this->colorSupportIsHex($value);
        } elseif (array_key_exists("isRGB", $args)) {
            $value = $this->colorSupportLSLVector($value, 255);
        } else {
            $value = $this->colorSupportLSLVector($value);
        }
        /*
        [OLD]
        if (array_key_exists("Convert", $args)) {
            $value = $this->colorSupportConvert($value, $args);
        }
        */
        return $value;
    }

    /**
     * filterDate
     * using the MM/DD/YYYY format
     * attempts checks on a input
     * supports args: asUNIX, humanReadable
     */
    protected function filterDate(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        $timeTest = explode("/", str_replace(" ", "", $value));
        if (count($timeTest) != 3) {
            $this->testOK = false;
            $this->whyfailed = "Bad formating";
            return null;
        }
        if (($timeTest[0] < 1) || ($timeTest[0] > 12)) {
            $this->whyfailed = "Month out of range";
            $this->testOK = false;
            return null;
        } elseif (($timeTest[1] < 1) || ($timeTest[1] > 31)) {
            $this->whyfailed = "Day out of range";
            $this->testOK = false;
            return null;
        } elseif (($timeTest[2] < 1970) || ($timeTest[2] > 2999)) {
            $this->whyfailed = "Year out of range";
            $this->testOK = false;
            return null;
        }
        if (array_key_exists("asUNIX", $args)) {
            $date = new \DateTime(
                $timeTest[2] . "-" . $timeTest[1] . "-" . $timeTest[0],
                new \DateTimeZone('Europe/London')
            );
            return $date->format("U");
        }
        if (array_key_exists("humanReadable", $args)) {
            return date('l jS \of F Y', strtotime(implode('/', $timeTest)));
        }
        return $value;
    }

    /**
     * filterEmail
     * Checks to see if a given string appears to be a vaild email
     * args: no_mailboxs
     * much faster but does not support gmail style + boxs.
     */
    protected function filterEmail(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        if (in_array("no_mailboxs", $args) == true) {
        // fails on ALOT of vaild email addresses. but much faster
            if (strpos($value, "+") !== false) {
                $this->whyfailed = "no_mailboxs";
                return null;
            }
            return $this->filterEmail($value);
        } else {
            $allowed = true;
            $local_value = "";
            $mailbox_value = "";
            $domain_value = "";
            $bits = explode("@", $value);
            if (count($bits) == 2) {
                $domain_value = $bits[1];
                $mailbox = explode("+", $bits[0]);
                $local_value = $mailbox[0];
                if (count($mailbox) == 2) {
                    $mailbox_value = $mailbox[1];
                }
                $filter_testvalue = "" . $local_value . "@" . $domain_value . "";
                if (filter_var($filter_testvalue, FILTER_VALIDATE_EMAIL) !== false) {
                    if ($mailbox_value != "") {
                        $value = "" . $local_value . "+" . $mailbox_value . "@" . $domain_value . "";
                    }
                } else {
                    $this->whyfailed = "Failed vaildation after removing mailbox";
                    $allowed = false;
                }
            } else {
                $this->whyfailed = "Required @ missing";
                $allowed = false;
            }
            if ($allowed == true) {
                return $value;
            }
            return null;
        }
    }

    /**
     * filterFloat
     * checks to see if the given input is a float.
     */
    protected function filterFloat(string $value, array $args = []): ?float
    {
        $this->filterIsString = false;
        $this->failure = false;
        $this->testOK = true;
        $value = floatval($value);
        if (array_key_exists("zeroCheck", $args)) {
            if ($value == "0") {
                $this->testOK = false;
                $this->whyfailed = "Zero value detected";
            }
        }
        if ($this->testOK) {
            return $value;
        }
        return null;
    }

        /**
     * filterUrl
     * checks to see if the given input is a url
     * can also enforce protocall with
     * isHTTP and isHTTPS args.
     */
    protected function setAndAtStart(string $value, string $match): ?string
    {
        $pos = strpos($value, $match);
        if ($pos === false) {
            $this->whyfailed = $match . " is missing from the value!";
            return null;
        } elseif ($pos != 0) {
            $this->whyfailed = $match . " is missing from the start of the value!";
            return null;
        }
        return $value;
    }
    protected function filterUrl(string $value, array $args = []): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            if (array_key_exists("isHTTP", $args)) {
                return $this->setAndAtStart($value, "http://");
            } elseif (array_key_exists("isHTTPS", $args)) {
                return $this->setAndAtStart($value, "https://");
            }
            return $value;
        }
        return null;
    }

    /**
     * filterInteger
     * checks to see if the given input is a int
     * supported args
     * zeroCheck - The number must not be zero
     * gtr0 - The number must be more than zero
     */
    protected function filterInteger(string $value, array $args = []): ?int
    {
        $this->filterIsString = false;
        $this->failure = false;
        $this->testOK = true;
        if (array_key_exists("zeroCheck", $args)) {
            if ($value == "0") {
                $this->testOK = false;
                $this->whyfailed = "rejected: Must not be zero";
                return null;
            }
        }
        $testValue = intval($value);
        if (array_key_exists("gtr0", $args)) {
            if ($testValue <= 0) {
                $this->testOK = false;
                $this->whyfailed = "To low ~ Min value: anything higher than zero";
                return null;
            }
        }
        $min = null;
        $max = null;
        if (array_key_exists("min", $args) == true) {
            $min = $args["min"];
        }
        if (array_key_exists("max", $args) == true) {
            $max = $args["max"];
        }
        if (($min != null) && ($max != null)) {
            if (($max < $min) && ($max != 0)) {
                $a = $min;
                $min = $max;
                $max = $a;
            }
        }
        if (($value < $min) && ($min != null)) {
            $this->whyfailed = "To low ~ Min value: " . $min;
            return null;
        }
        if (($value > $max) && ($max != null)) {
            $this->whyfailed = "To high ~ Max value: " . $max;
            return null;
        }
        if ($this->testOK) {
            return $testValue;
        }
        return null;
    }
    /**
     * filterJson
     * checks to see if the value can be decoded
     * into a json object
     * @return mixed[] or null
     */
    protected function filterJson(string $value, array $args = []): ?array
    {
        $this->filterIsString = false;
        $this->whyfailed = "";
        $json = json_decode($value, true);
        if (($json === false) || ($json === null)) {
            $this->whyfailed = "Not a vaild json object string";
            return null;
        } else {
            return $json;
        }
    }

    /**
     * filterString
     * checks to see if the input is a string
     * that passes the needed arg checks.
     * args ->
     * maxLength: the max length of the string
     * minLength: The min length of the string
     * - if the string is outside of the range min or max
     * null is returned.
     */
    protected function filterString(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        $min = 0;
        $max = 0;
        if (array_key_exists("minLength", $args) == true) {
            $min = $args["minLength"];
        }
        if (array_key_exists("maxLength", $args) == true) {
            $max = $args["maxLength"];
        }
        if (($max < $min) && ($max != 0)) {
            $a = $min;
            $min = $max;
            $max = $a;
        }
        $len = strlen($value);
        if ($len < $min) {
            $this->whyfailed = "To short ~ Min length: " . $min;
            return null;
        }
        if (($max > 0) && ($len > $max)) {
            $this->whyfailed = "To Long ~ Max length: " . $max;
            return null;
        }
        return $value;
    }

    /**
     * filterUuid
     * checks to see if the input is a vaild UUID
     * note: supports multiple specs.
     */
    protected function filterUuid(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        $uuid_specs = [
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-4[0-9A-Fa-f]{3}\-[89ABab][0-9A-Fa-f]{3}\-[0-9A-Fa-f]{12}$/i',
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}$/i',
        ];
        foreach ($uuid_specs as $spec) {
            if (preg_match($spec, $value)) {
                return $value;
            }
        }
        $this->whyfailed = "Not a vaild v1 or v4 uuid";
        return null;
    }

    /**
     * filterVector
     * checks to see if the input formated as a vector
     * args ->
     * strict: enforces starting < and ending >
     */
    protected function filterVector(string $inputvalue, array $args = []): ?string
    {
        $vector_specs = [
            '/^<(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)>$/i',
            // float/int vector with starting < and ending >
        ];
        $allow_no_arrows = true;
        if (array_key_exists("strict", $args) == true) {
            if ($args["strict"] == true) {
                $allow_no_arrows = false;
            }
        }
        if ($allow_no_arrows == true) {
            $vector_specs[] = '/^(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)$/i';
            // float/int vector without starting < and ending >
        }

        $this->filterIsString = false;
        foreach ($vector_specs as $spec) {
            if (preg_match($spec, $inputvalue)) {
                return $inputvalue;
            }
        }

        $this->whyfailed = "Did not match any vaild Vector patterns";
        return null;
    }
}
