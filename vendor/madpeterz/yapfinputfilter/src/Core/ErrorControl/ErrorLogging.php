<?php

namespace YAPF\Core\ErrorControl;

abstract class ErrorLogging
{
    protected $myLastError = "";
    protected $myLastErrorBasic = "";
    protected bool $enableErrorConsole = false;
    /**
     * addError
     * see getLastError()
     * $flileHint = file error happened on [send __FILE__]
     * $functionHint = function name [send __FUNCTION__]
     * $errorMessage = sent error message
     * $arrayAddon = extended return fields (not processed out to error logs)
     * @return mixed[] [status =>  false, message =>  string]
     */
    protected function addError(
        string $flileHint = "",
        string $functionHint = "",
        string $errorMessage = "",
        array $arrayAddon = []
    ): array {
        $this->myLastError = "File: " . $flileHint . " Function: " . $functionHint . " info: " . $errorMessage . "";
        $this->myLastErrorBasic = $errorMessage;
        if (($this->enableErrorConsole == true) || (defined("ErrorConsole") == true)) {
            error_log($this->myLastError);
        }
        return array_merge($arrayAddon, ["status" => false, "message" => $errorMessage]);
    }
    public function enableConsoleErrors(): void
    {
        $this->enableErrorConsole = true;
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
