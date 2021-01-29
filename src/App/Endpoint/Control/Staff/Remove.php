<?php

namespace App\Endpoint\Control\Staff;

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
        $this->setSwapTag("redirect", "staff");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "staff/manage/" . $this->page . "");
            return;
        }
        if ($staff->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find staff member");
            return;
        }
        if ($staff->getOwnerLevel() == true) {
            $this->setSwapTag(
                "message",
                "Unable to remove staff with owner level access via the web interface"
            );
            return;
        }
        $remove_status = $staff->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove staff member: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Staff member removed");
    }
}
