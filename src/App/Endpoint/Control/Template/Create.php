<?php

namespace App\Endpoint\Control\Template;

use App\R7\Model\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $name = $input->postString("name", 30, 5);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
            return;
        }
        $detail = $input->postString("detail", 800, 5);
        if ($detail == null) {
            $this->failed("Template failed:" . $input->getWhyFailed());
            return;
        }
        $notecardDetail = $input->postString("notecardDetail", 5000, 5);
        if ($notecardDetail == null) {
            $this->failed("Template failed:" . $input->getWhyFailed());
            return;
        }
        $template = new Template();
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $create_status = $template->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to create Template: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }
        $this->ok("Template created");
        $this->setSwapTag("redirect", "template");
    }
}
