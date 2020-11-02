<?php

namespace YAPF\Core;

abstract class SqlConnectedClass extends ErrorLogging
{
    protected $sql = null;
    protected $disabled = false;
    /**
     * __construct
     * if not marked as disabled connects the sql global value
     */
    protected function __construct()
    {
        global $sql;
        if ($this->disabled == false) {
            $this->sql = &$sql;
        }
    }
}
