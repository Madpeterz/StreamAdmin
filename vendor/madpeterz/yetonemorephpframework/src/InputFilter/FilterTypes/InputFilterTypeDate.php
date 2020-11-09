<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeDate extends InputFilterTypeEmail
{
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
        if (count($timeTest) == 3) {
            if (($timeTest[0] < 1) || ($timeTest[0] > 12)) {
                $this->whyfailed = "Month out of range";
                $this->testOK = false;
            } elseif (($timeTest[1] < 1) || ($timeTest[1] > 31)) {
                $this->whyfailed = "Day out of range";
                $this->testOK = false;
            } elseif (($timeTest[2] < 1970) || ($timeTest[2] > 2999)) {
                $this->whyfailed = "Year out of range";
                $this->testOK = false;
            }
        } else {
            $this->testOK = false;
            $this->whyfailed = "Year out of range";
        }
        if ($this->testOK) {
            $unix = strtotime(implode('/', $timeTest));
            if (array_key_exists("asUNIX", $args)) {
                return $unix;
            }
            if (array_key_exists("humanReadable", $args)) {
                return date('l jS \of F Y', $unix);
            }
            return $value;
        }
        return null;
    }
}
