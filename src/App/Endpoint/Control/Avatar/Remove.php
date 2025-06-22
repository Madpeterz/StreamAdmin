<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {
        $accept = $this->input->post("accept")->asString();
        if ($accept == null) {
            $this->failed("Accept button not triggered");
            return;
        }
        $this->setSwapTag("redirect", "avatar");
        $this->failed("ready");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "avatar/manage/" . $this->siteConfig->getPage() . "");
            $this->failed("Did not Accept");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find avatar");
            return;
        }
        $staff = $avatar->relatedStaff();
        if ($staff->getCount() > 0) {
            $this->failed("Unable to remove avatar its being used by a staff member");
            return;
        }
        $resellers = $avatar->relatedReseller();
        if ($resellers->getCount() > 0) {
            $this->failed("Unable to remove avatar its being used by a reseller");
            return;
        }
        $rentals = $avatar->relatedRental();
        if ($rentals->getCount() > 0) {
            $this->failed("Unable to remove avatar its being used by a rental client");
            return;
        }
        $transactions = $avatar->relatedTransactions();
        if ($transactions->getCount() > 0) {
            $result = $transactions->updateFieldInCollection(
                "avatarLink",
                $this->siteConfig->getSlConfig()->getOwnerAvatarLink()
            );
            if ($result->status == false) {
                $this->failed("Unable to reassign transactions from the old avatar to system owner");
                return;
            }
        }
        $auditlogs = $avatar->relatedAuditlog();
        $avatarName = $avatar->getAvatarName();
        $avid = $avatar->getId();
        if ($auditlogs->getCount() > 0) {
            $update = $auditlogs->updateFieldInCollection("avatarLink", 1);
            if ($update->status == false) {
                $this->failed("Unable to reassign audit logs from the old avatar to system");
                return;
            }
            $this->createAuditLog($avid, "audit logs changed assigned avatar", $avatarName, "system", "Auditlogs");
        }


        $remove_status = $avatar->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(sprintf("Unable to remove avatar: %1\$s", $remove_status->message));
            return;
        }
        $this->redirectWithMessage("Avatar removed");
        $this->createAuditLog($avid, "---", $avatarName);
    }
}
