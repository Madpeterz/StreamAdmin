<?php

namespace YAPF\InputFilter\Worker;

use YAPF\InputFilter\FilterTypes\InputFilterTypeColor as InputFilterTypeColor;

abstract class InputFilterWorkerValue extends InputFilterTypeColor
{
    /**
     * SharedInputFilter
     * fetchs the value from get or post
     * or returns the default
     * @return mixed
     */
    protected function sharedInputFilter(
        string $inputName,
        array &$source_dataset,
        string $filter = "string",
        array $args = []
    ) {
        $not_set = false;
        $value = $this->fetchTestingValue($not_set, $source_dataset, $inputName);
        if ($not_set == false) {
            $this->whyfailed = "";
            $value = $this->valueFilter($value, $filter, $args);
            if ($this->whyfailed != "") {
                $this->addError(__FILE__, __FUNCTION__, $this->whyfailed);
            }
        }
        return $this->failureExpectedReplyValue($value, $filter);
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
        $filter_tests = [
            "isNotEmpty" => [
                "expected" => true,
                "why" => "is empty",
            ],
            "is_null" => [
                "expected" => false,
                "why" => "is null",
            ],
            "is_object" => [
                "expected" => false,
                "why" => "is a object",
            ],
        ];
        if (in_array($filter, $this->filter_list) == false) {
            $this->whyfailed = "Unknown filter: " . $filter;
            return $this->failureExpectedReplyValue(null, $filter);
        }
        if (array_key_exists($filter, $this->filters) == true) {
            $filter_tests = array_merge($filter_tests, $this->filters[$filter]["tests"]);
        }
        if ($filter != "array") {
            $filter_tests["is_array"] = [
                "expected" => false,
                "why" => "is an array",
            ];
        }
        $this->whyfailed = "";
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
                $this->whyfailed = $test_config["why"];
                return null;
            }
        }
        $this->whyfailed = "Accepted filter not found";
        $filterfunction = "filter" . ucfirst($filter);
        if (method_exists($this, $filterfunction) == true) {
            $this->whyfailed = "";
            $value = $this->$filterfunction($value, $args);
            if ($this->whyfailed != "") {
                return $this->failureExpectedReplyValue($value, $filter);
            }
        }
        return $value;
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
