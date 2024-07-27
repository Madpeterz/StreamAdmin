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
        $this->objectType = "botcommandqserver";
        $this->taskNicename = "Bot commandQ crontask";
        $this->taskId = 3;
    }
}
