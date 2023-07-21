<?php

namespace App\Endpoint\Cronjob\Tasks;

use App\Endpoint\Cronjob\Master;
use App\Models\Avatar;
use App\Models\Botconfig;
use GuzzleHttp\Client;
use App\Endpoint\Secondlifeapi\Botcommandq\Next;

class Botcommandq extends Master
{
    protected ?Botconfig $botconfig = null;
    protected ?Avatar $botavatar = null;
    protected ?Client $httpClient = null;
    protected Next $task;

    public function __construct()
    {
        parent::__construct();
        $this->taskClass = new Next();
        $this->task = new Next();
        $this->objectType = "botcommandqserver";
        $this->taskNicename = "Bot commandQ crontask";
        $this->taskId = 3;
    }

    protected function doTask(): bool
    {
        if ($this->makeBotConfig() == false) {
            return false;
        }
        if ($this->makeBotAvatar() == false) {
            return false;
        }
        $this->task = new Next();
        $this->task->setCronConnected();
        $this->task->setOwnerOverride(true);
        $this->task->attachBotConfig($this->botconfig);
        if ($this->makeHTTPClient() == false) {
            return false;
        }
        $this->task->attachBotAvatar($this->botavatar);
        $this->task->process();
        $this->taskClass = $this->task;
        return true;
    }

    protected function makeHTTPClient(): bool
    {
        if ($this->httpClient != null) {
            $this->task->attachHTTPClient($this->httpClient);
            return true;
        }
        if ($this->httpClient == null) {
            $this->httpClient = $this->task->makeHTTPClient();
        }
        if ($this->httpClient == null) {
            $this->failed("Unable to get HTTP client");
            return false;
        }
        $this->task->attachHTTPClient($this->httpClient);
        return true;
    }

    protected function makeBotConfig(): bool
    {
        if ($this->botconfig != null) {
            return true;
        }
        $this->botconfig = new Botconfig();
        $this->botconfig->loadID(1);
        if ($this->botconfig->isLoaded() == false) {
            $this->botconfig = null;
            $this->failed("error - Unable to load bot config");
            return false;
        }
        if ($this->botconfig->getHttpMode() == false) {
            $this->failed("HTTP not enabled for bot");
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
            $this->failed("Unable to create avatar linked to bot");
            return false;
        }
        return true;
    }
}
