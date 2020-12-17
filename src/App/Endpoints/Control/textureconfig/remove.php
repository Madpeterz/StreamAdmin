<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "textureconfig");
$status = false;
if ($accept == "Accept") {
    $textureconfig = new textureconfig();
    if ($textureconfig->loadID($this->page) == true) {
        $remove_status = $textureconfigremoveEntry();
        if ($remove_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["textureconfig.rm.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["textureconfig.cr.error.13"], $remove_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["textureconfig.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["textureconfig.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "textureconfig/manage/" . $this->page . "");
}
