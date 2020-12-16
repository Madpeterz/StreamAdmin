<?php

$status = true;
$server = new server();
if ($server->loadID($this->page) == true) {
    if ($server->getApi_serverstatus() == 1) {
        $serverapi_helper = new serverapi_helper();
        $serverapi_helper->force_set_server($server);
        $apireply = $serverapi_helper->api_serverstatus();
        //print_r($apireply);
        if ($apireply["status"] == true) {
            $addon = "";
            if ($apireply["streams"]["total"] > 0) {
                $this->output->addSwapTagString("message", $addon);
                $percent = 100 - round((($apireply["streams"]["total"] - $apireply["streams"]["active"]) / $apireply["streams"]["total"]) * 100, 2);
                $text_color = "text-light";
                if ($percent < 40) {
                    $text_color = "text-danger";
                } elseif ($percent < 60) {
                    $text_color = "text-warning";
                } elseif ($percent < 80) {
                    $text_color = "text-info";
                }
                $this->output->addSwapTagString("message", "Str: <span class=\"" . $text_color . "\">" . $percent . " %</span>");
                $addon = " &nbsp;&nbsp;";
            }
            if ($apireply["loads"]["1"] > 0.0) {
                $this->output->addSwapTagString("message", $addon);
                $this->output->addSwapTagString("message", "CPU: <span class=\"text-light\">" . $apireply["loads"]["5"] . "</span>");
                $addon = " <br/>";
            }
            if ($apireply["ram"]["max"] > 0) {
                $this->output->addSwapTagString("message", $addon);
                $pcent = $apireply["ram"]["max"] / 100;
                $dif = $apireply["ram"]["max"] - $apireply["ram"]["free"];
                $pcents = 0;
                while ($dif > $pcent) {
                    $pcents++;
                    $dif -= $pcent;
                }
                $usage = $apireply["ram"]["max"] - $apireply["ram"]["free"];
                $mbmax = ($apireply["ram"]["max"] / 1000) / 1000;
                $mbusage = ($usage / 1000) / 1000;
                $max = round($mbmax, 2);
                $used = round($mbusage, 2);

                $text_color = "text-light";
                if ($pcents > 80) {
                    $text_color = "text-danger";
                } elseif ($pcents > 60) {
                    $text_color = "text-warning";
                } elseif ($pcents > 40) {
                    $text_color = "text-info";
                }
                $this->output->addSwapTagString("message", "Ram: <span class=\"" . $text_color . "\">" . $used . "/" . $max . " [" . $pcents . " %]</span>");
                $addon = " <br/>";
            }
            if ($this->output->get_swap_tag_string("message") == "") {
                $this->output->addSwapTagString("message", "<span class=\"text-info\">Online</span>");
            }
        } else {
            $this->output->addSwapTagString("message", "<span class=\"text-danger\">Offline</span>");
        }
    } else {
        $this->output->addSwapTagString("message", "<span class=\"text-warning\">Not supported</span>");
    }
} else {
    $this->output->setSwapTagString("message", "Unable to find server");
}
