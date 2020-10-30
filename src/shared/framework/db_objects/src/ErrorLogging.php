<?php

namespace Madpeterz\YAPF;

abstract class ErrorLogging
{
    protected $myLastError = "";
    /**
     * addError
     * Loggeds an error to error_log
     * and also sets myLastError
     * @return mixed[] [status =>  false, message =>  string]
     */
    protected function addError($file = "", $functionName = "", $additional = ""): array
    {
        $this->myLastError = "File: " . $file . " Function: " . $functionName . " info: " . $additional . "";
        error_log($this->myLastError);
        return ["status" => false, "message" => $additional];
    }
}
