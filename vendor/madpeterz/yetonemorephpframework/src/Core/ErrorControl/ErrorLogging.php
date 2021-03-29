<?php

namespace YAPF\Core\ErrorControl;

abstract class ErrorLogging
{
    protected $myLastError = "";
    protected $myLastErrorBasic = "";
    /**
     * addError
     * see getLastError()
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
