<?php

namespace YAPF\InputFilter\Worker;

abstract class InputFilterWorkerGet extends InputFilterWorkerValue
{
    /**
     * getFilter
     * fetchs the value from get and redirects to valueFilter
     * @return mixed or null
     */
    public function getFilter(string $inputName, string $filter = "string", array $args = [], $default = null)
    {
        $this->failure = false;
        $this->whyfailed = "No get value found with name: " . $inputName;
        $value = $default;
        if (isset($_GET[$inputName]) == false) {
            $this->failure = true;
        }
        if ($this->failure == false) {
            $this->whyfailed = "";
            $value = $this->valueFilter($_GET[$inputName], $filter, $args);
            if ($this->whyfailed != "") {
                $this->addError(__FILE__, __FUNCTION__, $this->whyfailed);
            }
        }
        return $this->failureExpectedReplyValue($value, $filter);
    }
}
