<?php

namespace App\Endpoint\SecondLifeApi\Bot;

use App\Helpers\BotHelper;
use App\Models\Botcommandq;
use App\Models\Sets\NotecardSet;
use App\Template\SecondlifeAjax;

class NotecardSync extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->failed("This API is owner only");
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
        $checks = $this->input->varInput(
            $this->siteConfig->getSlConfig()->getHttpInboundSecret()
        )->checkStringLength(5, 30)->asString();
        if ($checks != $this->siteConfig->getSlConfig()->getHttpInboundSecret()) {
            $this->failed("HTTP inbound secret has issues");
            return;
        }

        $notecardSet = new NotecardSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [0],
            "types" => ["i"],
            "matches" => [">"],
        ];
        $count_data = $notecardSet->countInDB($whereConfig);
        if ($count_data === null) {
            $this->failed("Unable to check if there are any notecards to send");
            return;
        }

        if ($count_data == 0) {
            $this->ok("nowork");
            return;
        }

        $BotcommandQ = new Botcommandq();
        if ($BotcommandQ->loadByCommand("FetchNextNotecard")->status == true) {
            $this->ok("nowork");
            return;
        }

        $bot_helper = new BotHelper();
        $botUUID = $bot_helper->getBotUUID();
        if ($botUUID == null) {
            $this->failed("Unable to load bot UUID");
            return;
        }
        $reply = $bot_helper->sendBotNextNotecard(
            $this->siteConfig->getSiteURL(),
            $this->siteConfig->getSlConfig()->getHttpInboundSecret()
        );
        if ($reply->status == false) {
            $this->failed("Unable to add fetch next notecard to bot Q");
            return;
        }
        $this->ok("ok");
    }
}
