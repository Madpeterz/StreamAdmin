<?php

namespace YAPF\Core\SQLi;

use YAPF\Core\ErrorControl\ErrorLogging;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

abstract class SqlConnectedClass extends ErrorLogging
{
    /* @var YAPF\MySQLi\MysqliEnabled $sql */
    protected ?MysqliConnector $sql;
    protected $disabled = false;
    /**
     * __construct
     * if not marked as disabled connects the sql global value
     */
    public function getLastSql(): string
    {
        if ($this->sql != null) {
            return $this->sql->getLastSql();
        }
        return "";
    }
    public function __construct()
    {
        global $sql;
        if ($this->disabled == false) {
            $this->sql = &$sql;
        }
    }
    public function reconnectSql(&$SetSQl): void
    {
        global $sql;
        $this->sql = &$this->unref($sql);
        $this->sql = $SetSQl;
    }
    protected function &unref($var): ?MysqliConnector
    {
        return $var;
    }
}
