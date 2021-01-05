<?php

namespace App\Endpoints\Control\Notice;

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
        if ($notice->loadByField("hoursremaining", $hoursremaining) == true) {
            $this->output->setSwapTagString("message", "There is already a notice assigned to that remaining hours");
            return;
        }
        if ($static_notecard->loadID($notice_notecardlink) == false) {
            $this->output->setSwapTagString("message", "Unable to find selected static notecard");
            return;
        }
        if ($static_notecard->getMissing() == true) {
            $this->output->setSwapTagString("message", "Selected static notecard is marked as missing please change!");
            return;
        }

        $notice = new Notice();
        $notice->setName($name);
        $notice->setImmessage($immessage);
        $notice->setUsebot($usebot);
        $notice->setHoursremaining($hoursremaining);
        $notice->setSend_notecard($send_notecard);
        $notice->setNotecarddetail($notecarddetail);
        $notice->setNotice_notecardlink($static_notecard->getId());
        $create_status = $notice->createEntry();
        if ($create_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to create notice: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Notice created");
        $this->output->setSwapTagString("redirect", "notice");
    }
}
