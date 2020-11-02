<?php

namespace YAPF\InputFilter\Worker;

use YAPF\InputFilter\FilterTypes\InputFilterTypeColor as InputFilterTypeColor;

abstract class InputFilterWorkerValue extends InputFilterTypeColor
{
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
        if ($value != null) {
            if (is_object($value) == true) {
                $this->failure = true;
                $this->whyfailed = "InputFilter can not deal with objects you crazy person";
                return null;
            } elseif (is_string($value) == true) {
                $this->testOK = true;
                $fast_test_numbers = ["integer","double","float"];
                if (in_array($filter, $fast_test_numbers) == true) {
                    if (is_numeric($value) == false) {
                        $this->failure = true;
                        $this->testOK = false;
                        $this->whyfailed = "Expects value to be numeric but its not";
                    }
                }
                if ($this->testOK == true) {
                    if ($filter == "string") {
                        $value = $this->filterString($value, $args);
                    } elseif ($filter == "integer") {
                        $value = $this->filterInteger($value, $args);
                    } elseif (($filter == "double") || ($filter == "float")) {
                        $value = $this->filterFloat($value, $args);
                    } elseif ($filter == "checkbox") {
                        $value = $this->filterCheckbox($value, $args);
                    } elseif ($filter == "bool") {
                        $value = $this->filterBool($value, $args);
                    } elseif (($filter == "uuid") || ($filter == "key")) {
                        $value = $this->filterUUID($value, $args);
                    } elseif ($filter == "vector") {
                        $value = $this->filterVector($value, $args);
                    } elseif ($filter == "date") {
                        $value = $this->filterDate($value, $args);
                    } elseif ($filter == "email") {
                        $value = $this->filterEmail($value, $args);
                    } elseif ($filter == "url") {
                        $value = $this->filterUrl($value, $args);
                    } elseif ($filter == "color") {
                        $value = $this->filterColor($value, $args);
                    } elseif ($filter == "trueFalse") {
                        $value = $this->filterTrueFalse($value);
                    } elseif ($filter == "json") {
                        $value = $this->filterJson($value);
                    }
                    if ($value !== null) {
                        return $value;
                    }
                    $this->failure = true;
                }
            } else {
                if ($filter == "array") {
                    return $this->filterArray($value, $args);
                } else {
                        $value = null;
                        $this->whyfailed = "Type error expected a string but got somthing else";
                }
            }
        } else {
            $this->failure = true;
        }
        if ($this->failure == true) {
            if ($filter == "checkbox") {
                return 0;
            } elseif ($filter == "trueFalse") {
                return 0;
            }
        }
        return null;
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
