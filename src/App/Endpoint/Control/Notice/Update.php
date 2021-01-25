<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $static_notecard = new Noticenotecard();
        $name = $input->postFilter("name");
        $hoursRemaining = $input->postFilter("hoursRemaining", "integer");
        $imMessage = $input->postFilter("imMessage");
        $useBot = $input->postFilter("useBot", "bool");
        $sendNotecard = $input->postFilter("sendNotecard", "bool");
        $notecardDetail = $input->postFilter("notecardDetail");
        $noticeNotecardLink = $input->postFilter("noticeNotecardLink", "integer");
        if ($sendNotecard == false) {
            if (strlen($notecardDetail) < 1) {
                $notecardDetail = "";
            }
        }
        $failed_on = "";
        $this->setSwapTag("redirect", null);
        if (strlen($name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 100) {
            $this->setSwapTag("message", "Name length must be 100 or less");
            return;
        }
        if (strlen($imMessage) < 5) {
            $this->setSwapTag("message", "imMessage length must be 5 or more");
            return;
        }
        if (strlen($imMessage) > 800) {
            $this->setSwapTag("message", "imMessage length must be 800 or less");
            return;
        }
        if (strlen($hoursRemaining) < 0) {
            $this->setSwapTag("message", "hoursRemaining must be 0 or more");
            return;
        }
        if (strlen($hoursRemaining) > 999) {
            $this->setSwapTag("message", "hoursRemaining must be 999 or less");
            return;
        }
        if ($static_notecard->loadID($noticeNotecardLink) == false) {
            $this->setSwapTag("message", "Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->setSwapTag("message", "Selected static notecard is marked as missing");
            return;
        }

        $notice = new Notice();
        if ($notice->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find notice");
            $this->setSwapTag("redirect", "notice");
            return;
        }
        $whereConfig = [
            "fields" => ["hoursRemaining"],
            "values" => [$hoursRemaining],
            "types" => ["i"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($notice->getTable(), $whereConfig);
        $expected_count = 0;
        if ($notice->getHoursRemaining() == $hoursRemaining) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag("message", "Unable to check if there is a notice assigned already");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->setSwapTag("message", "There is already a notice with that hours remaining trigger");
            return;
        }
        $notice->setName($name);
        $notice->setImMessage($imMessage);
        $notice->setUseBot($useBot);
        $notice->setSendNotecard($sendNotecard);
        $notice->setNotecardDetail($notecardDetail);
        $notice->setNoticeNotecardLink($static_notecard->getId());
        if (in_array($this->page, [6,10]) == false) {
            $notice->setHoursRemaining($hoursRemaining);
        }
        $update_status = $notice->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update notice: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Notice updated");
        $this->setSwapTag("redirect", "notice");
    }
}
