<?php

namespace App\Template;

use App\R7\Model\Slconfig;

class View extends TableView
{
    public function getSlConfigObject(): Slconfig
    {
        return $this->slconfig;
    }
}
