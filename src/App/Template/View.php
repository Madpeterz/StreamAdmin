<?php

namespace App\Template;

use App\Models\Slconfig;

class View extends TableView
{
    public function getSlConfigObject(): Slconfig
    {
        return $this->slconfig;
    }
}
