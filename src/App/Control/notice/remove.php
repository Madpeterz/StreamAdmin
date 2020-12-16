<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "notice");
$status = false;
if ($accept == "Accept") {
    if (in_array($this->page, [6,10]) == false) {
        $notice = new notice();
        if ($notice->loadID($this->page) == true) {
            $notecard_set = new notecard_set();
            $load_status = $notecard_set->loadOnField("noticelink", $notice->getId());
            if ($load_status["status"] == true) {
                if ($notecard_set->getCount() == 0) {
                    $remove_status = $noticeremoveEntry();
                    if ($remove_status["status"] == true) {
                        $status = true;
                        $this->output->setSwapTagString("message", $lang["notice.rm.info.1"]);
                    } else {
                        $this->output->setSwapTagString("message", sprintf($lang["notice.rm.error.4"], $remove_status["message"]));
                    }
                } else {
                    $this->output->setSwapTagString("message", sprintf($lang["notice.rm.error.6"], $notecard_set->getCount()));
                }
            } else {
                $this->output->setSwapTagString("message", $lang["notice.rm.error.5"]);
            }
        } else {
            $this->output->setSwapTagString("message", $lang["notice.rm.error.3"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["notice.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["notice.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "notice/manage/" . $this->page . "");
}
