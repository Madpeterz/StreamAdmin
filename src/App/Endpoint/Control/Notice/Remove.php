<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    protected ?int $newNoticeLevel = 0;
    protected Notice $notice = new Notice();
    protected Notice $transferNotice = new Notice();
    protected function postChecks(): bool
    {
        $this->setSwapTag("redirect", "notice");

        if ($this->input->post("accept")->asString() != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "notice/manage/" . $this->siteConfig->getPage() . "");
            return false;
        }
        $this->newNoticeLevel = $this->input->post("newNoticeLevel")->checkGrtThanEq(1)->asInt();
        if ($this->newNoticeLevel == null) {
            $this->failed("Unable to find transfer notice level");
            $this->setSwapTag("redirect", "notice/manage/" . $this->siteConfig->getPage() . "");
            return false;
        }
        if (in_array($this->siteConfig->getPage(), [6,10]) == true) {
            $this->failed("Selected notice is protected");
            return false;
        }
        return true;
    }
    protected function transferChecks(): bool
    {
        if ($this->transferNotice->loadID($this->newNoticeLevel)->status == false) {
            $this->failed("Unable to load transfer notice");
            return false;
        }
        if ($this->transferNotice->getId() == $this->notice->getId()) {
            $this->failed("Not sure how you did it but the transfer 
            notice and current notice can not be the same!");
            return false;
        }
        return true;
    }
    public function process(): void
    {
        if ($this->postChecks() == false) {
            return;
        }
        if ($this->notice->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to load selected notice");
            return;
        }
        $noticeid = $this->notice->getId();
        $noticename = $this->notice->getName();
        if ($this->transferChecks() == false) {
            return;
        }

        $notice_set = $this->notice->relatedRentalnoticeptout();
        if ($notice_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove notice it is being used by %1\$s pending notecards!",
                    $notice_set->getCount()
                )
            );
            return;
        }

        $RentalSet = $this->notice->relatedRental();

        $reply = true;
        $transfered_count = 0;
        if ($RentalSet->getCount() > 0) {
            $check = $RentalSet->updateFieldInCollection("noticeLink", $this->transferNotice->getId());
            $reply = $check->status;
            $transfered_count = $check->changes;
        }
        if ($reply == false) {
            $this->failed("Failed to transfer rentals to the new notice level");
            return;
        }
        $remove_status = $this->notice->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf("Unable to remove notice: %1\$s", $remove_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Notice removed");
        $this->createAuditLog($noticeid, "---", $noticename);
        if ($transfered_count > 0) {
            $this->redirectWithMessage(
                sprintf("Notice removed and transfered: %1\$s clients", $transfered_count)
            );
        }
    }
}
