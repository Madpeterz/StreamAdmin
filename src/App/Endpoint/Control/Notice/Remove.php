<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Sets\NotecardSet;
use App\Models\Notice;
use App\Models\Sets\RentalSet;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $notice = new Notice();
        $notecard_set = new NotecardSet();

        $accept = $this->input->post("accept");
        $newNoticeLevel = $input->postInteger("newNoticeLevel", false, true);
        $this->setSwapTag("redirect", "notice");
        if ($newNoticeLevel == null) {
            $this->failed("Unable to find transfer notice level");
            $this->setSwapTag("redirect", "notice/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "notice/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if (in_array($this->siteConfig->getPage(), [6,10]) == true) {
            $this->failed("Selected notice is protected");
            return;
        }

        if ($notice->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to load selected notice");
            return;
        }

        $transferNotice = new Notice();
        if ($transferNotice->loadID($newNoticeLevel) == false) {
            $this->failed("Unable to load transfer notice");
            return;
        }

        if ($transferNotice->getId() == $notice->getId()) {
            $this->failed("Not sure how you did it but the transfer 
            notice and current notice can not be the same!");
            return;
        }
        $load_status = $notecard_set->loadOnField("noticeLink", $notice->getId());
        if ($load_status["status"] == false) {
            $this->failed(
                "Unable to check if notice is being used by any pending notecards"
            );
            return;
        }
        if ($notecard_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove notice it is being used by %1\$s pending notecards!",
                    $notecard_set->getCount()
                )
            );
            return;
        }

        $RentalSet = new RentalSet();
        $whereConfig = [
            "fields" => ["noticeLink"],
            "values" => [$notice->getId()],
        ];
        $reply = $RentalSet->loadWithConfig($whereConfig);
        if ($reply["status"] == false) {
            $this->failed("Unable to check if Notice is being used.");
            return;
        }
        $reply = true;
        $transfered_count = 0;
        if ($RentalSet->getCount() > 0) {
            $check = $RentalSet->updateFieldInCollection("noticeLink", $transferNotice->getId());
            $reply = $check["status"];
            $transfered_count = $check["changes"];
        }
        if ($reply == false) {
            $this->failed("Failed to transfer rentals to the new notice level");
            return;
        }
        $remove_status = $notice->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove notice: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Notice removed");
        if ($transfered_count > 0) {
            $this->ok(
                sprintf("Notice removed and transfered: %1\$s clients", $transfered_count)
            );
        }
    }
}
