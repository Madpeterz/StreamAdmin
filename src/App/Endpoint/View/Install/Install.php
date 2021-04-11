<?php

namespace App\Endpoint\View\Install;

use App\R7\Model\Avatar;
use YAPF\MySQLi\MysqliEnabled;

class Install extends View
{
    public function process(): void
    {
        parent::process();
        $this->setSwapTag("page_content", "");
        $this->setSwapTag("html_title", "Installer / Step 3 / Install sql");
        $this->setSwapTag("page_title", "Installer / Step 3 / Install sql");
        $this->sql = new MysqliEnabled();
        $avatar = new Avatar();
        $avatar->ExpectedSqlLoadError(true); // suppress the warning about the table not existing
        if ($avatar->loadID(1) == true) {
            $this->setSwapTag("page_content", 'Error: DB has data unable to install!');
            return;
        }
        $install_file = ROOTFOLDER . "/App/Versions/installer.sql";
        if (file_exists($install_file) == false) {
            $this->setSwapTag("page_content", 'Error: Unable to find install sql file!');
            return;
        }
        $status = $this->sql->RawSQL($install_file, true);
        if ($status["status"] == false) {
            $this->setSwapTag("page_content", 'Error: installing db file: ' . $status["message"]);
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadID(1) == false) {
            $this->setSwapTag("page_content", 'Error: reading from datatabase');
            return;
        }
        if ($avatar->getAvatarUid() != "system") {
            $this->setSwapTag("page_content", 'Error: Expected install config db value is invaild');
            return;
        }
        $this->setSwapTag(
            "page_content",
            '
            <div class="alert alert-success" role="alert">Streamadmin DB installed [OK]
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
            <a href="[[url_base]]install/setup"><button class="btn btn-primary btn-block" type="button">
            Setup</button></a>'
        );
    }
}
