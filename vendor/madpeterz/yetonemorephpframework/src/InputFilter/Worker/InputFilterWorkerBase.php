<?php

namespace YAPF\InputFilter\Worker;

use YAPF\Core\ErrorLogging as ErrorLogging;

abstract class InputFilterWorkerBase extends ErrorLogging
{
    protected $failure = false;
    protected $testOK = true;
    protected $whyfailed = "";
    /**
     * getWhyFailed
     * returns the last stored fail message
     */
    public function getWhyFailed(): string
    {
        return $this->whyfailed;
    }
    /**
     * valueFilter
     * overridden later
     * @return mixed or null
     */
    public function valueFilter($value = null, string $filter = "", array $args = [])
    {
    }

    protected function isNotEmpty($input): bool
    {
        if (($input !== "0") && ($input !== 0)) {
            return !empty($input);
        }
        return true;
    }

    /**
     * scaleVector
     * takes a input vector and scales it but an amount.
     * @return mixed[] or null
     */
    protected function scaleVector(string $input, int $scaleby = 255): array
    {
        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $input));
        $vectorTest[0] *= $scaleby;
        $vectorTest[1] *= $scaleby;
        $vectorTest[2] *= $scaleby;
        return $vectorTest;
    }

    protected function valueInRange(float $min, float $max, float $value): bool
    {
        if ($value < $min) {
            return false;
        } elseif ($value > $max) {
            return false;
        }
        return true;
    }
}
