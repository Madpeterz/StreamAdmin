<?php

namespace App\Endpoint\Control\Staff;

use App\R7\Model\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $staff = new Staff();

        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "staff");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "staff/manage/" . $this->page . "");
            return;
        }
        if ($staff->loadID($this->page) == false) {
            $this->failed("Unable to find staff member");
            return;
        }
        if ($staff->getOwnerLevel() == true) {
            $this->failed(
                "Unable to remove staff with owner level access via the web interface"
            );
            return;
        }
        $remove_status = $staff->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove staff member: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Staff member removed");
    }
}
