<?php

namespace App\Endpoint\Control\Notice;

use App\R7\Set\NotecardSet;
use App\R7\Model\Notice;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $notice = new Notice();
        $notecard_set = new NotecardSet();

        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "notice");
        $status = false;
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "notice/manage/" . $this->page . "");
            return;
        }
        if (in_array($this->page, [6,10]) == true) {
            $this->setSwapTag("message", "Selected notice is protected");
            return;
        }

        if ($notice->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find notice");
            return;
        }
        $load_status = $notecard_set->loadOnField("noticeLink", $notice->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag(
                "message",
                "Unable to check if notice is being used by any pending notecards"
            );
            return;
        }
        if ($notecard_set->getCount() != 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove notice it is being used by %1\$s pending notecards!",
                    $notecard_set->getCount()
                )
            );
            return;
        }
        $remove_status = $notice->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove notice: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Notice removed");
    }
}
