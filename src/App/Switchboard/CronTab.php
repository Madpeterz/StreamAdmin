<?php

namespace App\Switchboard;

use YAPF\Bootstrap\Switchboard\Switchboard;

class CronTab extends Switchboard
{
    protected $targetEndpoint = "CronJob";
    public function __construct()
    {
        global $system;
        $this->config = $system;
        $options = $this->getOpts();
        if (array_key_exists("t", $options) == false) {
            echo "task arg t is missing unable to continue: " . json_encode($options);
            die();
        }
        $this->config->setModule("Tasks");
        $this->config->setArea($options["t"]);
        parent::__construct();
    }
}
