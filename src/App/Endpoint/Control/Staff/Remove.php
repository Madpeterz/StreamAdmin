<?php

namespace App\Endpoints\Control\Staff;

use App\Models\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $staff = new Staff();

        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "staff");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "staff/manage/" . $this->page . "");
            return;
        }
        if ($staff->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find staff member");
            return;
        }
        if ($staff->getOwnerLevel() == true) {
            $this->output->setSwapTagString(
                "message",
                "Unable to remove staff with owner level access via the web interface"
            );
            return;
        }
        $remove_status = $staff->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove staff member: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "staff member removed");
    }
}
