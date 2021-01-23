<?php

namespace App\Endpoint\View\Home;

use App\Template\Grid;

class DefaultView extends HomeDisplayData
{
    public function process(): void
    {
        $this->main_grid = new Grid();
        $this->loadDatasets();
        $this->displayDatasets();

        $this->output->addSwapTagString("page_content", $this->main_grid->getOutput());
    }
}
