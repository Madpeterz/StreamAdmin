<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {

        $notice = new Notice();
        $static_notecard = new Noticenotecard();
        $input = new InputFilter();
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
        if ($notice->loadByField("hoursRemaining", $hoursRemaining) == true) {
            $this->setSwapTag("message", "There is already a notice assigned to that remaining hours");
            return;
        }
        if ($noticeNotecardLink < 1) {
            $this->setSwapTag("message", "You must select a static notecard or use the non option!");
            return;
        }
        if ($static_notecard->loadID($noticeNotecardLink) == false) {
            $this->setSwapTag("message", "Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->setSwapTag("message", "Selected static notecard is marked as missing please change!");
            return;
        }

        $notice = new Notice();
        $notice->setName($name);
        $notice->setImMessage($imMessage);
        $notice->setUseBot($useBot);
        $notice->setHoursRemaining($hoursRemaining);
        $notice->setSendNotecard($sendNotecard);
        $notice->setNotecardDetail($notecardDetail);
        $notice->setNoticeNotecardLink($static_notecard->getId());
        $create_status = $notice->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create notice: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Notice created");
        $this->setSwapTag("redirect", "notice");
    }
}
