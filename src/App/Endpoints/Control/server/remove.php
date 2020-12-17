<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "server");
$status = false;
if ($accept == "Accept") {
    $server = new server();
    if ($server->loadID($this->page) == true) {
        $stream_set = new stream_set();
        $load_status = $stream_set->loadOnField("serverlink", $server->getId());
        if ($load_status["status"] == true) {
            if ($stream_set->getCount() == 0) {
                $api_requests_set = new api_requests_set();
                $load_status = $api_requests_set->loadOnField("serverlink", $server->getId());
                if ($load_status["status"] == true) {
                    if ($api_requests_set->getCount() == 0) {
                        $remove_status = $serverremoveEntry();
                        if ($remove_status["status"] == true) {
                            $status = true;
                            $this->output->setSwapTagString("message", $lang["server.rm.info.1"]);
                        } else {
                            $this->output->setSwapTagString("message", sprintf($lang["server.rm.error.3"], $remove_status["message"]));
                        }
                    } else {
                        $this->output->setSwapTagString("message", sprintf($lang["server.rm.error.6"], $api_requests_set->getCount()));
                    }
                } else {
                    $this->output->setSwapTagString("message", $lang["server.rm.error.7"]);
                }
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["server.rm.error.5"], $stream_set->getCount()));
            }
        } else {
            $this->output->setSwapTagString("message", $lang["server.rm.error.4"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["server.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["server.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "server/manage/" . $this->page . "");
}
