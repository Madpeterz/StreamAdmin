<?php

namespace YAPF\InputFilter\FilterTypes;

use YAPF\InputFilter\Worker\InputFilterWorkerBase as InputFilterWorkerBase;

abstract class InputFilterTypeArray extends InputFilterWorkerBase
{
    /**
     * filterArray
     * does a quick check if the input is an array.
     * @return mixed[] or null
     */
    protected function filterArray($value, array $args = []): ?array
    {
        // used by groupped inputs
        if (is_array($value) == true) {
            return $value;
        } else {
            $this->whyfailed = "not an array";
            return null;
        }
    }
}
