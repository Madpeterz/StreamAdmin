<?php

namespace YAPF\InputFilter\Worker;

abstract class InputFilterWorkerPost extends InputFilterWorkerGet
{
    /**
     * postFilter
     * fetchs the value from post and redirects to valueFilter
     * @return mixed or null
     */
    public function postFilter(string $inputName, string $filter = "string", array $args = [])
    {
        $this->whyfailed = "No post value found with name: " . $inputName;
        return $this->sharedInputFilter($inputName, $_POST, $filter, $args);
    }
}
