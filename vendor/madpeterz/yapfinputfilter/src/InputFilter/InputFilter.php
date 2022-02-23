<?php

namespace YAPF\InputFilter;

use PhpParser\Node\Expr\Cast\Double;
use YAPF\InputFilter\Rules;

class InputFilter extends Rules
{
    public function asHumanReadable(): ?string
    {
        if ($this->valueAsHumanReadable != null) {
            return $this->valueAsHumanReadable;
        }
        return $this->valueAsString;
    }

    public function asString(): ?string
    {
        return $this->valueAsString;
    }

    public function asFloat(): ?float
    {
        return $this->valueAsFloat;
    }

    public function asInt(): ?int
    {
        return $this->valueAsInt;
    }

    public function asBool(): bool
    {
        return $this->valueAsBool;
    }

    /**
     * asArray
     * returns the result as an array or null
     * @return ?mixed[]
     */
    public function asArray(): ?array
    {
        return $this->valueAsArray;
    }
}
