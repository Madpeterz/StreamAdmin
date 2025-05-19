<?php

namespace App\Endpoint\Secondlifeapi\Bot;

use App\Helpers\BotHelper;
use App\Models\Botcommandq;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\NotecardSet;
use App\Template\SecondlifeAjax;

class Notecardsync extends SecondlifeAjax
{
    protected BotHelper $botHelper;

    protected function setupHelper(): bool
    {
        $this->botHelper = new BotHelper();
        if ($this->botHelper->getNotecards() == false) {
            $this->failed("Notecards not enabled on bot");
            $this->setSwapTag("hassyncmessage", "2");
            return false;
        }
        if ($this->botHelper->getBotUUID() == null) {
            $this->failed("Unable to load bot UUID");
            return false;
        }
        $checks = $this->input->varInput(
            $this->siteConfig->getSlConfig()->getHttpInboundSecret()
        )->checkStringLength(5, 30)->asString();
        if ($checks != $this->siteConfig->getSlConfig()->getHttpInboundSecret()) {
            $this->failed("HTTP inbound secret has issues");
            return false;
        }
        return true;
    }

    protected function checkHaveStaticNotecardTask(): bool
    {
        $notecardSet = new NotecardSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [0],
            "types" => ["i"],
            "matches" => [">"],
        ];
        $count_data = $notecardSet->countInDB($whereConfig);
        if ($count_data->status == false) {
            $this->failed("Unable to check if there are any notecards to send");
            return false;
        }
        if ($count_data->items > 0) {
            $this->failed("pending static notecards");
            return false;
        }
        $this->ok("nowork");
        return true;
    }

    protected function checkHaveBotCommandTask(): bool
    {
        $botcommandQSet = new BotcommandqSet();
        if ($botcommandQSet->countInDB()->items == 0) {
            // nothing todo
            $this->ok("nowork");
        }

        $BotcommandQ = new Botcommandq();
        $BotcommandQ->loadByCommand("FetchNextNotecard");
        if ($BotcommandQ->isLoaded() == false) {
            $this->ok("nowork");
            return false;
        }
        return true;
    }

    protected function nextBotTask(): void
    {
        $this->setSwapTag("haserrormessage", false);
        $reply = $this->botHelper->sendBotNextNotecard(
            $this->siteConfig->getSiteURL(),
            $this->siteConfig->getSlConfig()->getHttpInboundSecret()
        );
        if ($reply->status == false) {
            $this->failed("Unable to add fetch next notecard to bot Q");
            return;
        }
        $this->ok("ok");
    }

    public function process(): void
    {
        $this->failed("checking owner_override");
        if ($this->owner_override == false) {
            $this->failed("This API is owner only");
            return;
        }
        $this->failed("checking setupHelper");
        if ($this->setupHelper() == false) {
            return;
        }
        $this->failed("checking checkHaveStaticNotecardTask");
        if ($this->checkHaveStaticNotecardTask() == false) {
            return;
        }
        $this->failed("checking checkHaveBotCommandTask");
        if ($this->checkHaveBotCommandTask() == false) {
            return;
        }
        $this->failed("checking nextBotTask");
        $this->nextBotTask();
    }
}
