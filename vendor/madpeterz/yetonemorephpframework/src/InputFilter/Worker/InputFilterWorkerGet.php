<?php

namespace YAPF\InputFilter\Worker;

abstract class InputFilterWorkerGet extends InputFilterWorkerValue
{
    /**
     * getFilter
     * fetchs the value from get and redirects to valueFilter
     * @return mixed or null
     */
    public function getFilter(string $inputName, string $filter = "string", array $args = [])
    {
        $this->whyfailed = "No get value found with name: " . $inputName;
        return $this->sharedInputFilter($inputName, $_GET, $filter, $args);
    }
}
