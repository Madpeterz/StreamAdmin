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

    protected function isNotEmpty($input): bool
    {
        if (($input !== "0") && ($input !== 0)) {
            return !empty($input);
        }
        return true;
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

        /**
     * SharedInputFilter
     * Overridden in InputFilterWorkerValue
     * @return mixed
     */
    protected function sharedInputFilter(
        string $inputName,
        array &$source_dataset,
        string $filter = "string",
        array $args = []
    ) {
        $not_set = false;
        $value = null;
        $this->whyfailed = "Using base function!";
        return $this->failureExpectedReplyValue($value, $filter);
    }
    /**
     * fetchTestingValue
     * fetchs the value from get or post
     * or returns the default
     * @return mixed
     */
    protected function fetchTestingValue(bool &$not_set, array &$source_dataset, string $name = "")
    {
        if (isset($source_dataset[$name]) == true) {
            return $source_dataset[$name];
        }
        $not_set = true;
        $this->failure = true;
        return null;
    }

    protected $filter_list = [
        "string",
        "integer",
        "float",
        "checkbox",
        "bool",
        "uuid",
        "vector",
        "date",
        "email",
        "url",
        "color",
        "truefalse",
        "json",
        "array",
    ];
    protected $filters = [
        "array" => [
            "tests" => [
                "is_array" => [
                    "expected" => true,
                    "why" => "Not an array",
                ],
            ],
        ],
        "integer" => [
            "tests" => [
                "is_numeric" => [
                    "expected" => true,
                    "why" => "not numeric",
                ],
            ],
        ],
        "float" => [
            "tests" => [
                "is_numeric" => [
                    "expected" => true,
                    "why" => "not numeric",
                ],
            ],
        ],
    ];

    /**
     * failureExpectedReplyValue
     * if a filter results in a Failure some filters
     * expect a non null reply
     * @return mixed
     */
    protected function failureExpectedReplyValue($value, string $filter)
    {
        if ($value === null) {
            if (in_array($filter, ["checkbox", "truefalse"]) == true) {
                return 0;
            }
        }
        return $value;
    }
}
