<?php

namespace App\Endpoint\Control\Staff;

use App\Models\Sets\AuditlogSet;
use App\Models\Staff;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {

        $staff = new Staff();
        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "staff");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "staff/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($staff->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find staff member");
            return;
        }
        if ($staff->getOwnerLevel() == true) {
            $this->failed(
                "Unable to remove staff with owner level access via the web interface"
            );
            return;
        }
        $avatar = $staff->relatedAvatar()->getFirst();
        $staffid = $staff->getId();
        $remove_status = $staff->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf("Unable to remove staff member: %1\$s", $remove_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Staff member removed");
        $this->createAuditLog($staffid, "---", $avatar->getAvatarName());
    }
}
