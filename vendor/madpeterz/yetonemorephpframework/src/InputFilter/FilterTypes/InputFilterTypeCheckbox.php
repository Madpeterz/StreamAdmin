<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeCheckbox extends InputFilterTypeVector
{
    /**
     * filterCheckbox
     * filters as integer by default
     * but if filter is set in the args
     * can filter by any other filter type.
     * @return mixed or null
     */
    protected function filterCheckbox(string $value, array $args = [])
    {
        $filter_as = "integer";
        $this->failure = true;
        $this->testOK = false;
        if (is_array($args) == true) {
            if (count($args) > 0) {
                if (array_key_exists("filter", $args) == true) {
                    $filter_as = $args["filter"];
                }
            }
        }
        $filter_as = "filter_" . $filter_as;
        if ($filter_as != "filterCheckbox") {
            if (method_exists($this, $filter_as) == true) {
                return $this->$filter_as($value, $args);
            }
            $this->whyfailed = "Unable to find filter to use";
            return null;
        }
        $this->whyfailed = "filter self loop detected";
        return null;
    }
}
