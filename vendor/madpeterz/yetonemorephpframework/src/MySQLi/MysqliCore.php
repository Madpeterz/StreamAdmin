<?php

namespace YAPF\MySQLi;

use App\Db as Db;

abstract class MysqliCore extends Db
{
    protected $sqlConnection = null;
    protected $hadErrors = false;
    protected $needToSave = false;
    public $lastSql = "";
    protected $track_table_select_access = false;
    protected $track_select_from_tables = [];

    public function __destruct()
    {
        $this->shutdown();
    }

    public function shutdown(): bool
    {
        $this->myLastErrorBasic = "Not connected";
        $result = true;
        if ($this->sqlConnection != null) {
            $result = $this->sqlSave();
        }
        return $result;
    }
    /**
     * getLastSql
     * returns the last SQL statement processed
     * good if you want to check what its doing
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }
}
