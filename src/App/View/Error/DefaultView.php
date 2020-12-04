<?php

namespace App\View\Error;

use App\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_content", "Sorry something has gone wrong!");
    }
}
