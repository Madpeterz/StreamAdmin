<?php

namespace App\View\Install;

use YAPF\MySQLi\MysqliEnabled;

class Test extends View
{
    public function process(): void
    {
        parent::process();
        $this->output->setSwapTagString("page_content", "");
        $this->output->setSwapTagString("html_title", "Installer / Step 2 / Test DB config");
        $this->output->setSwapTagString("page_title", "Installer / Step 2 / Test DB config");
        if (defined("DBCONFIGFOUND") == false) {
            $this->noConfig();
            return;
        }
        if ($this->sql == null) {
            global $sql;
            $sql = new MysqliEnabled();
            $this->sql = $sql;
        }
        if ($this->sql->sqlStart() == false) {
            $this->noConnection();
            return;
        }
        $this->output->addSwapTagString("page_content", '
            <div class="alert alert-success" role="alert">Connected [OK]
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
            <a href="install"><button class="btn btn-primary btn-block" type="button">Install</button></a>
            <br/><br/><br/><hr/><p>Do not use this option unless told to!</p>
            <a href="setup">
            <button class="btn btn-warning btn-block" type="button">Skip install - Goto setup</button></a>
            ');
    }
    protected function noConnection(): void
    {
        if (file_exists("../App/Config/db_installed.php") == true) {
            unlink("../App/Config/db_installed.php");
            $this->output->addSwapTagString("page_content", '
            <div class="alert alert-warning" role="alert">Error unable to connect to DB
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
        <a href=""><button class="btn btn-success btn-block" type="button">Back</button></a>
        ');
        } else {
            $this->output->addSwapTagString("page_content", '
            <div class="alert alert-warning" role="alert">Docker ENV config invaild
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
        Your installer has failed - Please contact support
        ');
        }
    }
    protected function noConfig(): void
    {
        $this->output->addSwapTagString("page_content", '
            <h4 class="btn btn-warning btn-block" type="button">Failed to find DB config!</h4><br/>
            Your installer has failed - Please contact support
            ');
    }
}
