<?php

namespace App\Endpoint\Cronjob\Tasks;

use App\Endpoint\Cronjob\Master;
use App\Endpoint\Secondlifeapi\Detailsserver\Next;

class Detailsserver extends Master
{
    public function __construct()
    {
        parent::__construct();
        $this->taskClass = new Next();
        $this->objectType = "detailsserver";
        $this->taskNicename = "Details server crontask";
        $this->taskId = 1;
        $this->create = true;
    }
}
