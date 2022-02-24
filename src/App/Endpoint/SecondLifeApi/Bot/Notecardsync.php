<?php

namespace App\Endpoint\SecondLifeApi\Bot;

use App\Helpers\BotHelper;
use App\Models\Botcommandq as ModelBotcommandq;
use App\Models\Notecard;
use App\Template\SecondlifeAjax;

class Notecardsync extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "This API is owner only");
            return;
        }
        $bot_helper = new BotHelper();
        if ($bot_helper->getNotecards() == false) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("hassyncmessage", "2");
            $this->setSwapTag("message", "Notecards not enabled on bot");
            return;
        }
        $this->setSwapTag("haserrormessage", false);
        $len = strlen($this->slconfig->getHttpInboundSecret());
        if ($len < 5) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("haserrormessage", true);
            $this->setSwapTag("message", "httpcode is to short - unable to continue");
            return;
        } elseif ($len > 30) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("haserrormessage", true);
            $this->setSwapTag("message", "httpcode is to long - unable to continue");
            return;
        }


        $notecard = new Notecard();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [0],
            "types" => ["i"],
            "matches" => [">"],
        ];
        $count_data = $this->siteConfig->getSQL()->basicCountV2($notecard->getTable(), $whereConfig);
        if ($count_data["status"] == false) {
            $this->failed("Unable to fetch next notecard");
            return;
        }

        if ($count_data["count"] == 0) {
            $this->ok("nowork");
            return;
        }

        $BotcommandQ = new ModelBotcommandq();
        if ($BotcommandQ->loadByCommand("FetchNextNotecard") == true) {
            $this->ok("nowork");
            return;
        }

        $bot_helper = new BotHelper();
        $botUUID = $bot_helper->getBotUUID();
        if ($botUUID == null) {
            $this->failed("Unable to load bot UUID");
            return;
        }

        global $template_parts;
        $reply = $bot_helper->sendBotNextNotecard($template_parts["SITE_URL"], $this->slconfig->getHttpInboundSecret());
        if ($reply == false) {
            $this->failed("Unable to add fetch next notecard to bot Q");
            return;
        }
        $this->ok("ok");
    }
}
