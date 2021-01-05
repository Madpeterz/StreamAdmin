<?php

namespace App\Endpoints\View\Bot;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
    }
}
