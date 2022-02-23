<?php

namespace YAPF\Framework\Core\SQLi;

use YAPF\Framework\Cache\Cache;
use YAPF\Core\ErrorControl\ErrorLogging;
use YAPF\Framework\MySQLi\MysqliEnabled as MysqliConnector;

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
        global $system;
        if ($this->disabled == false) {
            $this->sql = $system->getSQL();
            $this->cache = $system->getCacheDriver();
        }
    }

    public function reconnectSql(MysqliConnector &$SetSQl): void
    {
        if ($this->sql != null) {
            $this->sql = &$this->unref($this->sql);
        }
        $this->sql = $SetSQl;
    }
    protected function &unref($var): ?MysqliConnector
    {
        return $var;
    }

    protected ?Cache $cache = null;
    public function attachCache(Cache &$forceAttach): void
    {
        if ($this->cache != null) {
            $this->cache = $this->unrefCache($this->cache);
        }
        $this->cache = $forceAttach;
    }
    protected function &unrefCache($var): ?Cache
    {
        return $var;
    }
}
