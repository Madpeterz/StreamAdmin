<?php

namespace YAPF\Core;

abstract class ErrorLogging
{
    protected $myLastError = "";
    protected $myLastErrorBasic = "";
    /**
     * addError
     * Loggeds an error to trigger_error at E_USER_NOTICE
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
        $this->myLastErrorBasic = $er;
        trigger_error($this->myLastError, E_USER_NOTICE);
        return array_merge($ext, ["status" => false, "message" => $er]);
    }
    public function getLastErrorBasic(): string
    {
        return $this->myLastErrorBasic;
    }
    public function getLastError(): string
    {
        return $this->myLastError;
    }
}
