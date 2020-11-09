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
        if ($filter_as != "checkbox") {
            return $this->valueFilter($value, $filter_as);
        }
        $this->whyfailed = "filter self loop detected";
        return null;
    }
}
