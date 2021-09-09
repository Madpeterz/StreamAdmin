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
        $hoursRemaining = $input->postInteger("hoursRemaining");
        $imMessage = $input->postString("imMessage", 800, 5);
        if ($imMessage == null) {
            $this->failed("IM message failed:" . $input->getWhyFailed());
            return;
        }
        $useBot = $input->postFilter("useBot", "bool");
        $sendNotecard = $input->postFilter("sendNotecard", "bool");
        $notecardDetail = $input->postFilter("notecardDetail");
        $noticeNotecardLink = $input->postFilter("noticeNotecardLink", "integer");
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

        if ($noticeNotecardLink < 1) {
            $this->failed("You must select a static notecard or use the non option!");
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
