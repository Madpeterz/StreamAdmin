<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeBool extends InputFilterTypeFloat
{
    /**
     * filterBool
     * Checks if the value is in the array
     * if not returns false.
     */
    protected function filterBool(string $value, array $args = []): bool
    {
        $this->failure = false;
        $this->testOK = true;
        return in_array($value, ["true",true,1,"yes","True",true,"TRUE"]);
    }
    /**
     * filterTrueFalse
     * uses FilterBool but converts to 1 or 0
     */
    protected function filterTruefalse(string $value, array $args = []): int
    {
        $value = filterBool($value);
        if ($value === true) {
            return 1;
        }
        return 0;
    }
}
