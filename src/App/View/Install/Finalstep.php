<?php

namespace App\View\Install;

use App\Models\Slconfig;
use YAPF\MySQLi\MysqliEnabled;

class Finalstep extends View
{
    public function process(): void
    {
        parent::process();
        $this->output->setSwapTagString("html_title", "Installer / Step 5 / Finishing touchs");
        $this->output->setSwapTagString("page_title", "Installer / Step 5 / Finishing touchs");
        $this->sql = new MysqliEnabled();
        $slconfig = new Slconfig();
        if ($slconfig->loadID(1) == false) {
            $this->output->setSwapTagString(
                "page_content",
                "Setup finished<br/> SL link code: "
                . $slconfig->getSllinkcode() . "<br/>if you are running in docker please set: INSTALL_OK to 1"
            );
            return;
        }
        if (getenv('DB_HOST') === false) {
            file_put_contents("../App/Config/ready.txt", "ready");
        }
        $this->output->setSwapTagString(
            "page_content",
            "Setup finished<br/> SL link code: " . $slconfig->getSllinkcode()
            . "<br/>if you are running in docker please set: INSTALL_OK to 1 and restart the app!"
        );
        $this->output->addSwapTagString(
            "page_content",
            '<a href="[[url_base]]"><button class="btn btn-primary btn-block" type="button">Goto login</button></a>'
        );
    }
}
