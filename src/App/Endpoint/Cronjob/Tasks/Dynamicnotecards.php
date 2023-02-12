<?php

namespace App\Endpoint\Cronjob\Tasks;

use App\Endpoint\Cronjob\Master;
use App\Endpoint\Secondlifeapi\Bot\Notecardsync;

class Dynamicnotecards extends Master
{
    public function __construct()
    {
        parent::__construct();
        $this->taskClass = new Notecardsync();
        $this->objectType = "notecardsserver";
        $this->taskNicename = "Dynamic notecards crontask";
        $this->taskId = 2;
    }
}
