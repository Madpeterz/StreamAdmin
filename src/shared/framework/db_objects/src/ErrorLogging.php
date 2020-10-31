<?php

namespace YAPF;

abstract class ErrorLogging
{
    protected $myLastError = "";
    /**
     * addError
     * Loggeds an error to error_log
     * and also sets myLastError
     * $fl = file error happened on
     * $fn = function name
     * $er = sent error message
     * $ext = extended return fields (not processed out to error logs)
     * @return mixed[] [status =>  false, message =>  string]
     */
    protected function addError(string $fl = "", string $fn = "", string $er = "", array $ext = []): array
    {
        $this->myLastError = "File: " . $fl . " Function: " . $fn . " info: " . $er . "";
        error_log($this->myLastError);
        return array_merge($ext, ["status" => false, "message" => $additional]);
    }
}
