<?php

namespace App\Endpoints\Control\Server;

use App\Models\Server;
use App\Template\ViewAjax;
use serverapi_helper;

class ServerLoad extends ViewAjax
{
    public function process(): void
    {
        $status = true;
        $server = new Server();
        $serverapi_helper = new serverapi_helper();
        if ($server->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "<span class=\"text-danger\">Unable to find server</span>");
            return;
        }
        if ($server->getApi_serverstatus() == 0) {
            $this->output->addSwapTagString("message", "<span class=\"text-warning\">Not supported</span>");
            return;
        }
        $serverapi_helper->force_set_server($server);
        $apireply = $serverapi_helper->api_serverstatus();
        if ($apireply["status"] == false) {
            $this->output->addSwapTagString("message", "<span class=\"text-danger\">Offline</span>");
            return;
        }
        $this->output->addSwapTagString("status", "true");
        $addon = $this->getStreamInfo($apireply);
        $addon = $this->getCPUinfo($apireply, $addon);
        $addon = $this->getRamInfo($apireply, $addon);
        if ($this->output->getSwapTagString("message") == "") {
            $this->output->addSwapTagString("message", "<span class=\"text-info\">Online</span>");
        }
    }

    protected function getRamInfo(array $apireply, string $addon): string
    {
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
            $this->output->addSwapTagString(
                "message",
                "Ram: <span class=\"" . $text_color . "\">" . $used . "/" . $max . " [" . $pcents . " %]</span>"
            );
            $addon = " <br/>";
        }
        return $addon;
    }

    protected function getStreamInfo(array $apireply): string
    {
        $addon = "";
        if ($apireply["streams"]["total"] > 0) {
            $this->output->addSwapTagString("message", $addon);
            $diff = $apireply["streams"]["total"] - $apireply["streams"]["active"];
            $percent = 100 - round(
                ($diff  / $apireply["streams"]["total"]) * 100,
                2
            );
            $text_color = "text-light";
            if ($percent < 40) {
                $text_color = "text-danger";
            } elseif ($percent < 60) {
                $text_color = "text-warning";
            } elseif ($percent < 80) {
                $text_color = "text-info";
            }
            $this->output->addSwapTagString(
                "message",
                "Str: <span class=\"" . $text_color . "\">" . $percent . " %</span>"
            );
            $addon = " &nbsp;&nbsp;";
        }
        return $addon;
    }

    protected function getCPUinfo(array $apireply, string $addon): string
    {
        if ($apireply["loads"]["1"] > 0.0) {
            $this->output->addSwapTagString("message", $addon);
            $this->output->addSwapTagString(
                "message",
                "CPU: <span class=\"text-light\">" . $apireply["loads"]["5"] . "</span>"
            );
            $addon = " <br/>";
        }
        return $addon;
    }
}