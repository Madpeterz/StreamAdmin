<?php

namespace App\Endpoint\Control\Notice;

use App\R7\Model\Notice;
use App\R7\Model\Noticenotecard;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {

        $notice = new Notice();
        $static_notecard = new Noticenotecard();
        $input = new InputFilter();
        $name = $input->postString("name", 100, 5);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
            return;
        }
        $hoursRemaining = $input->postInteger("hoursRemaining", false, true);
        if ($hoursRemaining === null) {
            $this->failed("Hours remain failed:" . $input->getWhyFailed());
            return;
        }
        $imMessage = $input->postString("imMessage", 800, 5);
        if ($imMessage == null) {
            $this->failed("IM message failed:" . $input->getWhyFailed());
            return;
        }
        $sendObjectIM = $input->postBool("sendObjectIM");
        if ($sendObjectIM === null) {
            $this->failed("Send object IM failed:" . $input->getWhyFailed());
            return;
        }

        $useBot = $input->postBool("useBot");
        if ($sendObjectIM === null) {
            $this->failed("Use bot failed:" . $input->getWhyFailed());
            return;
        }
        $sendNotecard = $input->postBool("sendNotecard");
        if ($sendObjectIM === null) {
            $this->failed("Send notecard failed:" . $input->getWhyFailed());
            return;
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
        if ($sendNotecard == false) {
            if (strlen($notecardDetail) < 1) {
                $notecardDetail = "";
            }
        }

        $this->setSwapTag("redirect", null);

        if ($notice->loadByHoursRemaining($hoursRemaining) == true) {
            $this->failed("There is already a notice assigned to that remaining hours");
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
        $notice->setSendObjectIM($sendObjectIM);
        $notice->setName($name);
        $notice->setImMessage($imMessage);
        $notice->setUseBot($useBot);
        $notice->setHoursRemaining($hoursRemaining);
        $notice->setSendNotecard($sendNotecard);
        $notice->setNotecardDetail($notecardDetail);
        $notice->setNoticeNotecardLink($static_notecard->getId());
        $create_status = $notice->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
                sprintf("Unable to create notice: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->ok("Notice created");
        $this->setSwapTag("redirect", "notice");
    }
}
