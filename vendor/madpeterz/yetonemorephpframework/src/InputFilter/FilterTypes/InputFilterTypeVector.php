<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeVector extends InputFilterTypeDate
{
    /**
     * filterVector
     * checks to see if the input formated as a vector
     * args ->
     * strict: enforces starting < and ending >
     */
    protected function filterVector(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = false;
        $vectorTest = explode(",", str_replace(["<", " ", ">", "(", ")"], "", $value));
        if (count($vectorTest) == 3) {
            if (
                ($this->filter_float($vectorTest[0]) != null) &&
                ($this->filter_float($vectorTest[1]) != null) &&
                ($this->filter_float($vectorTest[2]) != null)
            ) {
                if (array_key_exists("strict", $args)) {
                    if ((substr_count($value, '<') != 1) || (substr_count($value, '>') != 1)) {
                        $this->whyfailed = "Strict mode: Required <  & > Missing";
                    } else {
                        $this->testOK = true;
                    }
                } else {
                    $this->testOK = true;
                }
            } else {
                $this->whyfailed = "the 3 Parts required to be floats or integers. example: 42.4,33,415.11";
            }
        } else {
            $this->whyfailed = "Require 3 parts split with , example: 23,42,55";
        }
        if ($this->testOK) {
            return $value;
        }
        return null;
    }
}
