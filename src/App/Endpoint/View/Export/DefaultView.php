<?php

namespace App\Endpoint\View\Export;

use YAPF\Bootstrap\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            return;
        }

        $config_areas = [];
        if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
            $config_areas["Style 1 -> [Clients and Streams]"] = [
                "link" => "Export/Flow1",
                "icon" => "fas fa-blender",
                "color" => "primary",
            ];
        }
        ksort($config_areas);
        $grid = new Grid();
        foreach ($config_areas as $key => $value) {
            $element = '
            <a target="_BLANK" href="[[SITE_URL]]' . $value["link"] . '">
            <button type="button" class="btn btn-' . $value["color"] . ' btn-lg btn-block mt-2 mb-3">
            <h5 class="text-black"><i class="' . $value["icon"] . '"></i></h5>
            ' . $key . '
            </button>
            </a>';
            $grid->addContent($element, 4);
        }
        $this->output->addSwapTagString("page_content", $grid->getOutput());
    }
}
