<?php

namespace App\View\Home;

use App\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        $dashboard_load_order = ["servers","server_loads","streams_status","notices","objects"];
        foreach ($dashboard_load_order as $load_file) {
            $loadobj = "../App/View/Home/Dashboard/Loaders/" . $load_file;
            include $loadobj;
        }

        $main_grid = new Grid();
        $dashboard_display_order = ["streams","clients","servers","objects","versions","final_normal","owner"];
        foreach ($dashboard_display_order as $load_file) {
            $loadobj = "../App/View/Home/Dashboard/Displays/" . $load_file;
            include $loadobj;
        }
        $this->output->addSwapTagString("page_content", $main_grid->getOutput());
    }
}
