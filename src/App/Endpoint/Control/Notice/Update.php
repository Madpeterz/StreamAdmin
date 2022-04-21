<?php

namespace App\Endpoint\Control\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Models\Sets\NoticeSet;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {
        $static_notecard = new Noticenotecard();
        $name = $this->input->post("name")->checkStringLength(5, 100)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $minValue = 1;
        if ($this->siteConfig->getPage() == 6) {
            $minValue = 0;
        }
        $hoursRemaining = $this->input->post("hoursRemaining")->checkGrtThanEq($minValue)->asInt();
        if ($hoursRemaining < $minValue) {
            $this->failed("Hours remaining failed:" . $this->input->getWhyFailed());
            return;
        }
        if ($this->siteConfig->getPage() == 6) {
            $hoursRemaining = 0;
        }
        $imMessage = $this->input->post("imMessage")->checkStringLength(5, 800)->asString();
        if ($imMessage === null) {
            $this->failed("IM message failed:" . $this->input->getWhyFailed());
            return;
        }
        $sendObjectIM = $this->input->post("sendObjectIM")->asBool();
        if ($sendObjectIM === null) {
            $sendObjectIM = false;
        }

        $useBot = $this->input->post("useBot")->asBool();
        if ($useBot === null) {
            $useBot = false;
        }
        $sendNotecard = $this->input->post("sendNotecard")->asBool();
        if ($sendNotecard === null) {
            $sendNotecard = false;
        }
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
        if ($static_notecard->loadID($noticeNotecardLink)->status == false) {
            $this->failed("Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->failed("Selected static notecard is marked as missing please change!");
            return;
        }

        $notice = new Notice();
        if ($notice->loadID($this->siteConfig->getPage())->status == false) {
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
        $noticeSet = new NoticeSet();
        $count_check = $noticeSet->countInDB($whereConfig);
        $expected_count = 0;
        if ($notice->getHoursRemaining() == $hoursRemaining) {
            $expected_count = 1;
        }
        if ($count_check === null) {
            $this->failed("Unable to check if there is a notice assigned already");
            return;
        }
        if ($count_check != $expected_count) {
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
        if (in_array($this->siteConfig->getPage(), [6,10]) == false) {
            $notice->setHoursRemaining($hoursRemaining);
        }
        $update_status = $notice->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update notice: %1\$s", $update_status->message)
            );
            return;
        }
        $this->ok("Notice updated");
        $this->setSwapTag("redirect", "notice");
    }
}
