<?php

namespace App\Endpoint\Control\Template;

use App\R7\Model\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $name = $input->postString("name", 30, 5);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
        }
        $detail = $input->postString("detail", 800, 5);
        if ($detail == null) {
            $this->failed("Template failed:" . $input->getWhyFailed());
        }
        $notecardDetail = $input->postString("notecardDetail", 1600, 5);
        if ($notecardDetail == null) {
            $this->failed("Template failed:" . $input->getWhyFailed());
        }
        $template = new Template();
        if ($template->loadID($this->page) == false) {
            $this->failed("Unable to find template");
            return;
        }
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $update_status = $template->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to update Template: %1\$s",
                    $update_status["message"]
                )
            );
            return;
        }
        $this->ok("Template updated");
        $this->setSwapTag("redirect", "template");
    }
}
