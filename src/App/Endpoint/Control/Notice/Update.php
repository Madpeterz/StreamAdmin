<?php

namespace App\Endpoint\Control\Notice;

use App\R7\Model\Notice;
use App\R7\Model\Noticenotecard;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $static_notecard = new Noticenotecard();
        $name = $input->postString("name", 100, 5);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
            return;
        }
        $hoursRemaining = $input->postInteger("hoursRemaining");
        $imMessage = $input->postString("imMessage", 800, 5);
        if ($imMessage == null) {
            $this->failed("IM message failed:" . $input->getWhyFailed());
            return;
        }
        $useBot = $input->postBool("useBot");
        $sendNotecard = $input->postBool("sendNotecard");
        $notecardDetail = $input->postString("notecardDetail");
        $noticeNotecardLink = $input->postInteger("noticeNotecardLink");
        if ($sendNotecard == false) {
            if (strlen($notecardDetail) < 1) {
                $notecardDetail = "";
            }
        }
        $this->setSwapTag("redirect", null);
        if (strlen($hoursRemaining) < 0) {
            $this->failed("hoursRemaining must be 0 or more");
            return;
        }
        if (strlen($hoursRemaining) > 999) {
            $this->failed("hoursRemaining must be 999 or less");
            return;
        }
        if ($static_notecard->loadID($noticeNotecardLink) == false) {
            $this->failed("Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->failed("Selected static notecard is marked as missing");
            return;
        }

        $notice = new Notice();
        if ($notice->loadID($this->page) == false) {
            $this->failed("Unable to find notice");
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
            $this->failed("Unable to check if there is a notice assigned already");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->failed("There is already a notice with that hours remaining trigger");
            return;
        }
        if ($notecardDetail == null) {
            $notecardDetail = " ";
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
            $this->failed(
                sprintf("Unable to update notice: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->ok("Notice updated");
        $this->setSwapTag("redirect", "notice");
    }
}
