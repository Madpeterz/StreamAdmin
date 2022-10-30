<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $notice = new Notice();
        $static_notecard = new Noticenotecard();
        $name = $this->input->post("name")->checkStringLength(5, 100)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $hoursRemaining = $this->input->post("hoursRemaining")->checkGrtThanEq(1)->asInt();
        if ($hoursRemaining === null) {
            $this->failed("Hours remain failed:" . $this->input->getWhyFailed());
            return;
        }
        $imMessage = $this->input->post("imMessage")->checkStringLength(5, 800)->asString();
        if ($imMessage == null) {
            $this->failed("IM message failed:" . $this->input->getWhyFailed());
            return;
        }
        $sendObjectIM = $this->input->post("sendObjectIM")->asBool();
        $useBot = $this->input->post("useBot")->asBool();
        $sendNotecard = $this->input->post("sendNotecard")->asBool();
        $notecardDetail = $this->input->post("notecardDetail")->asString();
        if ($notecardDetail === null) {
            $this->failed("Notecard detail failed:" . $this->input->getWhyFailed());
            return;
        }
        $noticeNotecardLink = $this->input->post("noticeNotecardLink")->checkGrtThanEq(1)->asInt();
        if ($noticeNotecardLink === null) {
            $this->failed("Static notecard failed:" . $this->input->getWhyFailed());
            return;
        }
        if ($sendNotecard == false) {
            if (nullSafeStrLen($notecardDetail) < 1) {
                $notecardDetail = "";
            }
        }

        $this->setSwapTag("redirect", null);

        if ($notice->loadByHoursRemaining($hoursRemaining)->status == true) {
            $this->failed("There is already a notice assigned to that remaining hours");
            return;
        }

        if ($static_notecard->loadID($noticeNotecardLink)->status == false) {
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
        if ($create_status->status == false) {
            $this->failed(
                sprintf("Unable to create notice: %1\$s", $create_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Notice created");
    }
}
