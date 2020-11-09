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
    protected function filterVector(string $inputvalue, array $args = []): ?string
    {
        $vectorTest = explode(",", str_replace(["<", " ", ">", "(", ")"], "", $inputvalue));
        if (count($vectorTest) != 3) {
            $this->whyfailed = "Require 3 parts split with , example: 23,42,55";
            return null;
        }

        $tests = [];
        $tests[] = $this->isNotEmpty($vectorTest[0]); // R
        $tests[] = $this->isNotEmpty($vectorTest[1]); // G
        $tests[] = $this->isNotEmpty($vectorTest[2]); // B
        if (in_array(false, $tests) == true) {
            $this->whyfailed = "nulls not allowed for values";
            return null;
        }

        if (array_key_exists("strict", $args) == true) {
            if ((substr_count($inputvalue, '<') != 1) || (substr_count($inputvalue, '>') != 1)) {
                $this->whyfailed = "Strict mode: Required <  & > Missing";
                return null;
            }
        }

        return $inputvalue;
    }
}
