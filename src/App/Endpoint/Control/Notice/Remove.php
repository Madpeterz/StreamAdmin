<?php

namespace App\Endpoints\Control\Notice;

use App\Models\NotecardSet;
use App\Models\Notice;
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
        $this->output->setSwapTagString("redirect", "notice");
        $status = false;
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "notice/manage/" . $this->page . "");
            return;
        }
        if (in_array($this->page, [6,10]) == true) {
            $this->output->setSwapTagString("message", "Selected notice is protected");
            return;
        }

        if ($notice->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find notice");
            return;
        }
        $load_status = $notecard_set->loadOnField("noticelink", $notice->getId());
        if ($load_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                "Unable to check if notice is being used by any pending notecards"
            );
            return;
        }
        if ($notecard_set->getCount() != 0) {
            $this->output->setSwapTagString(
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
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove notice: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Notice removed");
    }
}