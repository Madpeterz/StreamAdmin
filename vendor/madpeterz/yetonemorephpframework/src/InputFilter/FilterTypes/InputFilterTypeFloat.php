<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeFloat extends InputFilterTypeInteger
{
    /**
     * filterFloat
     * checks to see if the given input is a float.
     */
    protected function filterFloat(string $value, array $args = []): ?float
    {
        $this->failure = false;
        $this->testOK = true;
        if ($value === "") {
            $this->whyfailed = "Empty inside like me";
            return null;
        }
        if (is_float($value + 0) == false) {
            $this->whyfailed = "Not a float";
            return null;
        }
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
}
