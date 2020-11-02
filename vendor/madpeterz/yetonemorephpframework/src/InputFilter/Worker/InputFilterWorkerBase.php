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
}
