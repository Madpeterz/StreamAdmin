<?php

namespace App\Switchboard;

class CronTab extends ConfigEnabled
{
    protected string $targetEndpoint = "CronJob";
    public function __construct()
    {
        global $system;
        $this->siteConfig = $system;
        $options = $this->getOpts();
        if (array_key_exists("t", $options) == false) {
            echo "task arg t is missing unable to continue: " . json_encode($options);
            die();
        }
        $this->siteConfig->setModule("Tasks");
        $this->siteConfig->setArea($options["t"]);
        parent::__construct();
    }
}
