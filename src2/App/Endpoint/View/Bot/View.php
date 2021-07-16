<?php

namespace App\Endpoint\View\Bot;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
    }
}
