<?php

namespace App\Endpoints\SecondLifeApi\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\Notecard;
use App\Template\SecondlifeAjax;
use bot_helper;

class Notecardsync extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == true) {
            $this->output->setSwapTagString("message", "This API is owner only");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->output->setSwapTagString("message", "Unable to load bot config");
            return;
        }
        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load bot avatar");
            return;
        }
        if ($botconfig->getNotecards() == false) {
            $this->output->setSwapTagString("status", "true");
            $this->output->setSwapTagString("hassyncmessage", "2");
            $this->output->setSwapTagString("message", "Notecards not enabled on bot");
            return;
        }
        $notecard = new Notecard();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [0],
            "types" => ["i"],
            "matches" => [">"],
        ];
        $count_data = $this->sql->basicCountV2($notecard->getTable(), $whereConfig);
        if ($count_data["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to fetch next notecard");
            return;
        }

        $this->output->setSwapTagString("status", "true");
        if ($count_data["count"] == 0) {
            $this->output->setSwapTagString("message", "No work");
            $this->output->setSwapTagString("hassyncmessage", "0");
            return;
        }

        $this->output->setSwapTagString("hassyncmessage", "1");
        $this->output->setSwapTagString("avataruuid", $botavatar->getAvataruuid());
        $bot_helper = new bot_helper();
        $message = $bot_helper->send_bot_command(
            $botconfig,
            "fetchnextnotecard",
            [$this->output->getSwapTagString("url_base"),$this->slconfig->getHttp_inbound_secret()]
        );
        $this->output->setSwapTagString("message", $message);
    }
}
