<?php

namespace App\Endpoints\Control\Notice;

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
        $hoursremaining = $input->postFilter("hoursremaining", "integer");
        $immessage = $input->postFilter("immessage");
        $usebot = $input->postFilter("usebot", "bool");
        $send_notecard = $input->postFilter("send_notecard", "bool");
        $notecarddetail = $input->postFilter("notecarddetail");
        $notice_notecardlink = $input->postFilter("notice_notecardlink", "integer");
        if ($send_notecard == false) {
            if (strlen($notecarddetail) < 1) {
                $notecarddetail = "";
            }
        }
        $failed_on = "";
        $this->output->setSwapTagString("redirect", null);
        if (strlen($name) < 5) {
            $this->output->setSwapTagString("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 100) {
            $this->output->setSwapTagString("message", "Name length must be 100 or less");
            return;
        }
        if (strlen($immessage) < 5) {
            $this->output->setSwapTagString("message", "immessage length must be 5 or more");
            return;
        }
        if (strlen($immessage) > 800) {
            $this->output->setSwapTagString("message", "immessage length must be 800 or less");
            return;
        }
        if (strlen($hoursremaining) < 0) {
            $this->output->setSwapTagString("message", "hoursremaining must be 0 or more");
            return;
        }
        if (strlen($hoursremaining) > 999) {
            $this->output->setSwapTagString("message", "hoursremaining must be 999 or less");
            return;
        }
        if ($static_notecard->loadID($notice_notecardlink) == false) {
            $this->output->setSwapTagString("message", "Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->output->setSwapTagString("message", "Selected static notecard is marked as missing");
            return;
        }

        $notice = new Notice();
        if ($notice->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find notice");
            $this->output->setSwapTagString("redirect", "notice");
            return;
        }
        $whereConfig = [
            "fields" => ["hoursremaining"],
            "values" => [$hoursremaining],
            "types" => ["i"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($notice->getTable(), $whereConfig);
        $expected_count = 0;
        if ($notice->getHoursremaining() == $hoursremaining) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to check if there is a notice assigned already");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->output->setSwapTagString("message", "There is already a notice with that hours remaining trigger");
            return;
        }
        $notice->setName($name);
        $notice->setImmessage($immessage);
        $notice->setUsebot($usebot);
        $notice->setSend_notecard($send_notecard);
        $notice->setNotecarddetail($notecarddetail);
        $notice->setNotice_notecardlink($static_notecard->getId());
        if (in_array($this->page, [6,10]) == false) {
            $notice->setHoursremaining($hoursremaining);
        }
        $update_status = $notice->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to update notice: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Notice updated");
        $this->output->setSwapTagString("redirect", "notice");
    }
}
