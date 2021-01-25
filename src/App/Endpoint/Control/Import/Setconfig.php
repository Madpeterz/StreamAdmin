<?php

namespace App\Endpoint\Control\Import;

use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Setconfig extends ViewAjax
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
            $this->setSwapTag("message", "Only the system owner can access this area");
            $this->setSwapTag("redirect", "");
        }
        $input = new InputFilter();
        $db_host = $input->postFilter("db_host");
        $db_name = $input->postFilter("db_name");
        $db_username = $input->postFilter("db_username");
        $db_pass = $input->postFilter("db_pass");

        $saveconfig = '<?php $r4_db_host="' . $db_host . '"; $r4_db_name="'
        . $db_name . '"; $r4_db_username="' . $db_username . '"; $r4_db_pass="'
        . $db_pass . '";?>';
        if (file_exists("../App/Config/r4.php") == true) {
            unlink("../App/Config/r4.php");
        }
        file_put_contents("../App/Config/r4.php", $saveconfig);
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("redirect", "import");
    }
}
