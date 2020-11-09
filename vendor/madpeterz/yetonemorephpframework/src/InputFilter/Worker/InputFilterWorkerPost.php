<?php

namespace YAPF\InputFilter\Worker;

abstract class InputFilterWorkerPost extends InputFilterWorkerGet
{
    /**
     * postFilter
     * fetchs the value from post and redirects to valueFilter
     * @return mixed or null
     */
    public function postFilter(string $inputName, string $filter = "string", array $args = [], $default = null)
    {
        $this->failure = false;
        $this->whyfailed = "";
        $value = $default;
        if (isset($_POST[$inputName]) == false) {
            $this->failure = true;
        }
        if ($this->failure == false) {
            $value = $this->valueFilter($_POST[$inputName], $filter, $args);
            if ($this->whyfailed != "") {
                $this->addError(__FILE__, __FUNCTION__, $this->whyfailed);
            }
        }
        return $this->failureExpectedReplyValue($value, $filter);
    }
}
