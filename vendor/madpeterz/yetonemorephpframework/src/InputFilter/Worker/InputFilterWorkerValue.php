<?php

namespace YAPF\InputFilter\Worker;

use YAPF\InputFilter\FilterTypes\InputFilterTypeColor as InputFilterTypeColor;

abstract class InputFilterWorkerValue extends InputFilterTypeColor
{
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
            "min_passing_score" => 3,
            "tests" => [
                "is_array" => [
                    "expected" => true,
                    "crit" => true,
                    "why" => "Not an array",
                ],
            ],
        ],
        "integer" => [
            "min_passing_score" => 4,
            "tests" => [
                "is_numeric" => [
                    "expected" => true,
                    "crit" => true,
                    "why" => "not numeric",
                ],
            ],
        ],
        "float" => [
            "min_passing_score" => 4,
            "tests" => [
                "is_numeric" => [
                    "expected" => true,
                    "crit" => true,
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
    /**
     * valueFilter
     * filters the given value by the selected filter
     * using the optional args, see the filter for their
     * supports args.
     * @return mixed or null
     */
    public function valueFilter($value = null, string $filter = "", array $args = [])
    {
        $this->failure = false;
        if ($filter == "") {
            $filter = "string";
        }
        $filter_min_score = 3;
        $filter_tests = [
            "isNotEmpty" => [
                "expected" => true,
                "crit" => true,
                "why" => "is empty",
            ],
            "is_null" => [
                "expected" => false,
                "crit" => true,
                "why" => "is null",
            ],
            "is_object" => [
                "expected" => false,
                "crit" => true,
                "why" => "is a object",
            ],
        ];
        if (in_array($filter, $this->filter_list) == false) {
            $this->whyfailed = "Unknown filter: " . $filter;
            return $this->failureExpectedReplyValue(null, $filter);
        }
        if (array_key_exists($filter, $this->filters) == true) {
            $filter_min_score = $this->filters[$filter]["min_passing_score"];
            $filter_tests = array_merge($filter_tests, $this->filters[$filter]["tests"]);
        }
        if ($filter != "array") {
            $filter_min_score++;
            $filter_tests["is_array"] = [
                "expected" => false,
                "crit" => true,
                "why" => "is an array",
            ];
        }
        $score = 0;
        foreach ($filter_tests as $test_function => $test_config) {
            $result = "not processed";
            if ($test_function == "isNotEmpty") {
                if (is_array($value) == false) {
                    $result = $this->isNotEmpty($value);
                } else {
                    $result = true;
                }
            } else {
                $result = $test_function($value);
            }
            if ($result != $test_config["expected"]) {
                if ($test_config["crit"] == true) {
                    $this->whyfailed = $test_config["why"];
                    break;
                }
            }
            $score++;
        }
        if ($score < $filter_min_score) {
            if ($this->whyfailed == "") {
                $this->whyfailed = "Score to low";
            }
            return $this->failureExpectedReplyValue(null, $filter);
        }

        $filterfunction = "filter" . ucfirst($filter);
        if (method_exists($this, $filterfunction) == true) {
            $value = $this->$filterfunction($value, $args);
            if ($this->whyfailed != "") {
                return $this->failureExpectedReplyValue($value, $filter);
            }
            return $value;
        }
        $this->whyfailed = "Built in filter is missing!";
        return $this->failureExpectedReplyValue(null, $filter);
    }
    /**
     * varFilter
     * see: valueFilter
     * @return mixed or null
     */
    public function varFilter($currentvalue, string $filter = "string", array $args = [])
    {
        return $this->valueFilter($currentvalue, $filter, $args);
    }
}
