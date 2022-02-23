<?php

namespace YAPF\InputFilter;

abstract class Checks extends Base
{
    public function checkStartsWith(string $needle): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $pos = strpos($this->valueAsString, $needle);
        if ($pos === false) {
            $this->failed("Missing " . $needle . " from the string!");
        } elseif ($pos != 0) {
            $this->failed("Does not start with " . $needle);
        }
        return $this;
    }

    public function checkEndsWith(string $needle): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        $length = strlen($needle);
        if ($length == 0) {
            return $this;
        }
        if (substr($this->valueAsString, 0 - $length) === $needle) {
            return $this;
        }
        $this->failed("Does not end with " . $needle);
        return $this;
    }

    public function checkInRange(?float $min, ?float $max): InputFilter
    {
        if ($this->valueAsFloat == null) {
            return $this;
        }
        if (($min == null) || ($max == null)) {
            return $this;
        }
        if (($this->valueAsFloat < $min) || ($this->valueAsFloat > $max)) {
            $this->failed("Not in range: " . round($min, 2) . " to " . round($max));
        }
        return $this;
    }

    public function checkGrtThanEq(float $min): InputFilter
    {
        if ($this->valueAsFloat == null) {
            return $this;
        }
        if ($this->valueAsFloat < $min) {
            $this->failed("Less than: " . round($min, 2));
        }
        return $this;
    }

    public function checkLessThanEq(float $max): InputFilter
    {
        if ($this->valueAsFloat == null) {
            return $this;
        }
        if ($this->valueAsFloat > $max) {
            $this->failed("More than: " . round($max, 2));
        }
        return $this;
    }

    public function checkMatchRegex(string $regex): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        if (!preg_match($regex, $this->valueAsString)) {
            $this->failed("Did not match regex check");
        }
        return $this;
    }

    public function checkStringLength(?int $min, ?int $max): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        if (($min == null) || ($max == null)) {
            return $this;
        }
        $len = strlen($this->valueAsString);
        if (($len < $min) || ($len > $max)) {
            $this->failed("Invaild length of " . $len . " expected " . $min . " to " . $max);
        }
        return $this;
    }

    public function checkStringLengthMin(?int $min): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        if ($min == null) {
            return $this;
        }
        $len = strlen($this->valueAsString);
        if ($len < $min) {
            $this->failed("Invaild length of " . $len . " expected longer than " . $min);
        }
        return $this;
    }

    public function checkStringLengthMax(?int $max): InputFilter
    {
        if ($this->valueAsString == null) {
            return $this;
        }
        if ($max == null) {
            return $this;
        }
        $len = strlen($this->valueAsString);
        if ($len > $max) {
            $this->failed("Invaild length of " . $len . " expected less than " . $max);
        }
        return $this;
    }
}
