<?php

namespace App\CronJob\Tasks;

use App\CronJob\Master\Master;
use App\Endpoint\SecondLifeApi\Botcommandq\Next;
use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use GuzzleHttp\Client;

class BotcommandQ extends Master
{
    protected string $cronName = "botcommandqserver";
    protected int $cronID = 4;
    protected string $cronRunClass = "Not Used";

    protected ?Botconfig $botconfig = null;
    protected ?Avatar $botavatar = null;
    protected ?Client $httpClient = null;

    protected function makeBotConfig(): bool
    {
        if ($this->botconfig != null) {
            return true;
        }
        $this->botconfig = new Botconfig();
        $this->botconfig->loadID(1);
        if ($this->botconfig->isLoaded() == false) {
            $this->botconfig = null;
            echo "error - Unable to load bot config\n";
            return false;
        }
        return true;
    }
    protected function makeBotAvatar(): bool
    {
        if ($this->botavatar != null) {
            return true;
        }
        $this->botavatar = new Avatar();
        $this->botavatar->loadID($this->botconfig->getAvatarLink());
        if ($this->botavatar->isLoaded() == false) {
            $this->botavatar = null;
            echo "error - Unable to create avatar linked to bot\n";
            return false;
        }
        return true;
    }
    protected function doTask(): bool
    {
        if ($this->makeBotConfig() == false) {
            return false;
        }
        if ($this->makeBotAvatar() == false) {
            return false;
        }
        if ($this->botconfig->getHttpMode() == false) {
            echo "error - HTTP is not enabled for the bot but the cron is enabled.\n";
            return false;
        }
        $task = new Next();
        $task->setCronConnected();
        $task->setOwnerOverride(true);
        $task->attachBotAvatar($this->botavatar);
        $task->attachBotConfig($this->botconfig);
        if ($this->httpClient == null) {
            $this->httpClient = $task->makeHTTPClient();
        }
        if ($this->httpClient == null) {
            echo "error - Unable to get a HTTP client\n";
            return false;
        }
        $task->attachHTTPClient($this->httpClient);
        $task->process();
        if ($task->getOutputObject()->getSwapTagBool("status") == false) {
            echo "error - " . $task->getOutputObject()->getSwapTagString("message") . "\n";
            return false;
        }
        return true;
    }
}
