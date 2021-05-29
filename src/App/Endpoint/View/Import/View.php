<?php

namespace App\Endpoint\View\Import;

use App\Template\View as TemplateView;
use YAPF\MySQLi\MysqliEnabled;

abstract class View extends TemplateView
{
    protected ?MysqliEnabled $oldSqlDB = null;
    protected ?MysqliEnabled $realSqlDB = null;
    protected $sqlReady = false;
    protected $attemptedRead = false;

    public function __construct()
    {
        parent::__construct();
        global $sql, $r4_db_username, $r4_db_pass, $r4_db_name, $r4_db_host;

        $this->setSwapTag("html_title", "R4 import");
        $this->setSwapTag("page_title", "Import /");
        $this->setSwapTag("page_actions", "");

        if (getenv('R4_DB_HOST') !== false) {
            $r4_db_host = getenv('R4_DB_HOST');
            $r4_db_name = getenv('R4_DB_DATABASE');
            $r4_db_username = getenv('R4_DB_USERNAME');
            $r4_db_pass = getenv('R4_DB_PASSWORD');
            $this->attemptedRead = true;
        }
        if (file_exists("" . ROOTFOLDER . "/App/Config/r4.php") == true) {
            include "" . ROOTFOLDER . "/App/Config/r4.php";
            $this->attemptedRead = true;
        }

        if ($this->attemptedRead == true) {
            $this->realSqlDB = $sql;
            $this->oldSqlDB = new MysqliEnabled();
            $this->sqlReady = $this->oldSqlDB->sqlStartConnection(
                $r4_db_username,
                $r4_db_pass,
                $r4_db_name,
                false,
                $r4_db_host
            );
        }
    }
}
