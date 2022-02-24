<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Framework\ViewAjax;

class Create extends ViewAjax
{
    public function process(): void
    {

        $name = $this->input->post("name", 30, 5);
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $detail = $this->input->post("detail", 800, 5);
        if ($detail == null) {
            $this->failed("Template failed:" . $this->input->getWhyFailed());
            return;
        }
        $notecardDetail = $this->input->post("notecardDetail", 5000, 5);
        if ($notecardDetail == null) {
            $this->failed("Template failed:" . $this->input->getWhyFailed());
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
