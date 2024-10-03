<?php

namespace App\Switchboard;

class Ans extends ConfigEnabled
{
    protected string $targetEndpoint = "Ans";
    protected string $defaultModule = "Ans";
    protected string $defaultArea = "Event";
}
