<?php

namespace App\View\Install;

use App\Avatar;
use YAPF\MySQLi\MysqliEnabled;

class Install extends View
{
    public function process(): void
    {
        parent::process();
        $this->output->setSwapTagString("page_content", "");
        $this->output->setSwapTagString("html_title", "Installer / Step 3 / Install sql");
        $this->output->setSwapTagString("page_title", "Installer / Step 3 / Install sql");
        $this->sql = new MysqliEnabled();
        $avatar = new Avatar();
        if ($avatar->loadID(1) == true) {
            $this->output->setSwapTagString("page_content", 'Error: DB has data unable to install!');
            return;
        }
        $install_file = "../App/View/Install/Required/install.sql";
        if (file_exists($install_file) == false) {
            $this->output->setSwapTagString("page_content", 'Error: Unable to find install sql file!');
            return;
        }
        $status = $this->sql->RawSQL($install_file, true);
        if ($status["status"] == false) {
            $this->output->setSwapTagString("page_content", 'Error: installing db file: ' . $status["message"]);
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadID(1) == false) {
            $this->output->setSwapTagString("page_content", 'Error: reading from datatabase');
            return;
        }
        if ($avatar->getAvatar_uid() != "system") {
            $this->output->setSwapTagString("page_content", 'Error: Expected install config db value is invaild');
            return;
        }
        $this->output->setSwapTagString(
            "page_content",
            '
            <div class="alert alert-success" role="alert">Streamadmin DB installed [OK]
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
            <a href="setup"><button class="btn btn-primary btn-block" type="button">Setup</button></a>'
        );
    }
}
