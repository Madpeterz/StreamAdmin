<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Framework\ViewAjax;

class Create extends ViewAjax
{
    public function process(): void
    {

        $notice = new Notice();
        $static_notecard = new Noticenotecard();

        $name = $this->post("name")->checkStringLength(5, 100)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $hoursRemaining = $this->post("hoursRemaining")->checkGrtThanEq(1)->asInt();
        if ($hoursRemaining === null) {
            $this->failed("Hours remain failed:" . $this->input->getWhyFailed());
            return;
        }
        $imMessage = $this->post("imMessage")->checkStringLength(5, 800)->asString();
        if ($imMessage == null) {
            $this->failed("IM message failed:" . $this->input->getWhyFailed());
            return;
        }
        $sendObjectIM = $this->post("sendObjectIM")->asBool();

        $useBot = $this->post("useBot")->asBool();

        $sendNotecard = $this->post("sendNotecard")->asBool();

        $notecardDetail = $this->post("notecardDetail")->asString();
        if ($notecardDetail === null) {
            $this->failed("Notecard detail failed:" . $this->input->getWhyFailed());
            return;
        }

        $noticeNotecardLink = $this->post("noticeNotecardLink")->checkGrtThanEq(1)->asInt();
        if ($noticeNotecardLink === null) {
            $this->failed("Static notecard failed:" . $this->input->getWhyFailed());
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
