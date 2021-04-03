<?php

namespace App\Endpoint\View\Import;

use App\Template\View as TemplateView;
use YAPF\MySQLi\MysqliEnabled;

abstract class View extends TemplateView
{
    protected ?MysqliEnabled $oldSqlDB = null;
    protected ?MysqliEnabled $realSqlDB = null;
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "R4 import");
        $this->setSwapTag("page_title", "Import /");
        $this->setSwapTag("page_actions", "");
        if (file_exists("" . ROOTFOLDER . "/App/Config/r4.php") == true) {
            include "" . ROOTFOLDER . "/App/Config/r4.php";
            global $sql;
            $this->realSqlDB = $sql;
            $this->oldSqlDB = new MysqliEnabled();
            $this->oldSqlDB->sqlStartConnection($r4_db_username, $r4_db_pass, $r4_db_name, false, $r4_db_host);
        }
    }
}
