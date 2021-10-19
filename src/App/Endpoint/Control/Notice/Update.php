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
        $minValue = 1;
        if ($this->page == 6) {
            $minValue = 0;
        }
        $hoursRemaining = $input->postInteger("hoursRemaining");
        if ($hoursRemaining < $minValue) {
            $this->failed("Hours remain failed: can not be less than " . $minValue);
            return;
        }
        if ($this->page == 6) {
            $hoursRemaining = 0;
        }
        $imMessage = $input->postString("imMessage", 800, 5);
        if ($imMessage === null) {
            $this->failed("IM message failed:" . $input->getWhyFailed());
            return;
        }
        $sendObjectIM = $input->postBool("sendObjectIM");
        if ($sendObjectIM === null) {
            $sendObjectIM = false;
        }

        $useBot = $input->postBool("useBot");
        if ($useBot === null) {
            $useBot = false;
        }

        $sendNotecard = $input->postBool("sendNotecard");
        if ($sendNotecard === null) {
            $sendNotecard = false;
        }
        $notecardDetail = $input->postString("notecardDetail");
        if ($sendObjectIM === null) {
            $this->failed("Notecard detail failed:" . $input->getWhyFailed());
            return;
        }
        $noticeNotecardLink = $input->postInteger("noticeNotecardLink", false, true);
        if ($noticeNotecardLink === null) {
            $this->failed("Static notecard failed:" . $input->getWhyFailed());
            return;
        }
        if ($static_notecard->loadID($noticeNotecardLink) == false) {
            $this->failed("Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->failed("Selected static notecard is marked as missing please change!");
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
        $notice->setSendObjectIM($sendObjectIM);
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
