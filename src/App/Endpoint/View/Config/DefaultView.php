<?php

namespace App\Endpoint\View\Config;

use App\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        $check_objects = ["Server","Template","Package","Stream","Slconfig","Textureconfig"];
        $all_ok = true;
        foreach ($check_objects as $check) {
            $checkObj = "App\\R7\\Model\\" . $check;
            $obj = new $checkObj();
            if ($obj->HasAny() == false) {
                $all_ok = false;
                $this->output->redirect($check
                . "?bubblemessage=Please%20create%20a%20" . $check . "%20first!&bubbletype=warning");
                break;
            }
        }
        if ($all_ok == true) {
            $config_areas = [
                "Avatars" => [
                    "icon" => "fas fa-users",
                    "link" => "Avatar",
                ],
                "Template" => [
                    "icon" => "fas fa-indent",
                    "link" => "Template",
                ],
                "System config" => [
                    "icon" => "fas fa-tools",
                    "link" => "Slconfig",
                ],
                "Textures" => [
                    "icon" => "far fa-images",
                    "link" => "Textureconfig",
                ],
                "Transactions" => [
                    "icon" => "fas fa-credit-card",
                    "link" => "Transactions",
                ],
                "Notices" => [
                    "icon" => "fas fa-bullhorn",
                    "link" => "Notice",
                ],
                "Objects" => [
                    "icon" => "fas fa-cubes",
                    "link" => "Objects",
                ],
                "Servers" => [
                    "icon" => "fas fa-server",
                    "link" => "Server",
                ],
                "Datatables" => [
                    "icon" => "fas fa-table",
                    "link" => "Datatables",
                ],
            ];
            if ($this->session->getOwnerLevel() == 1) {
                $config_areas["R4 import"] = [
                    "icon" => "fas fa-cloud-upload-alt",
                    "link" => "Import",
                ];
                $config_areas["Bot"] = [
                    "icon" => "fas fa-robot",
                    "link" => "Bot",
                ];
                $config_areas["Staff"] = [
                    "icon" => "fas fa-user-lock",
                    "link" => "Staff",
                ];
                $config_areas["Banlist"] = [
                    "icon" => "fas fa-user-slash",
                    "link" => "Banlist",
                ];
                $config_areas["Export"] = [
                    "icon" => "fas fa-file-export",
                    "link" => "Export",
                ];
            }
            ksort($config_areas);
            $grid = new Grid();
            foreach ($config_areas as $key => $value) {
                $element = '
                <a href="[[url_base]]' . $value["link"] . '">
                <button type="button" class="btn btn-outline-success btn-lg btn-block mt-2 mb-3">
                <h5 class="text-black"><i class="' . $value["icon"] . '"></i></h5>
                ' . $key . '
                </button>
                </a>';
                $grid->addContent($element, 4);
            }
            $this->output->addSwapTagString("page_content", $grid->getOutput());
        }
    }
}
