<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeInteger extends InputFilterTypeString
{
    /**
     * filterInteger
     * checks to see if the given input is a int
     * supported args
     * zeroCheck - The number must not be zero
     * gtr0 - The number must be more than zero
     */
    protected function filterInteger(string $value, array $args = []): ?int
    {
        $this->failure = false;
        $this->testOK = true;
        if (array_key_exists("zeroCheck", $args)) {
            if ($value == "0") {
                $this->testOK = false;
                $this->whyfailed = "Zero value detected";
            }
        }
        if (is_numeric($value)) {
            $testValue = intval($value);
            if (array_key_exists("gtr0", $args)) {
                if ($testValue <= 0) {
                    $this->testOK = false;
                    $this->whyfailed = "Value must be more than zero";
                }
            }
            if ($this->testOK) {
                return $testValue;
            }
        } else {
            $this->whyfailed = "Not a number";
        }
        return null;
    }
}
