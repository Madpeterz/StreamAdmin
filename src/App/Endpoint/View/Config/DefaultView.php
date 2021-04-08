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
                    "link" => "avatar",
                ],
                "Template" => [
                    "icon" => "fas fa-indent",
                    "link" => "template",
                ],
                "System config" => [
                    "icon" => "fas fa-tools",
                    "link" => "slconfig",
                ],
                "Textures" => [
                    "icon" => "far fa-images",
                    "link" => "textureconfig",
                ],
                "Transactions" => [
                    "icon" => "fas fa-credit-card",
                    "link" => "transactions",
                ],
                "Notices" => [
                    "icon" => "fas fa-bullhorn",
                    "link" => "notice",
                ],
                "Objects" => [
                    "icon" => "fas fa-cubes",
                    "link" => "objects",
                ],
                "Servers" => [
                    "icon" => "fas fa-server",
                    "link" => "server",
                ],
            ];
            if ($this->session->getOwnerLevel() == 1) {
                $config_areas["R4 import"] = [
                    "icon" => "fas fa-cloud-upload-alt",
                    "link" => "import",
                ];
                $config_areas["Bot"] = [
                    "icon" => "fas fa-robot",
                    "link" => "bot",
                ];
                $config_areas["Staff"] = [
                    "icon" => "fas fa-user-lock",
                    "link" => "staff",
                ];
                $config_areas["Banlist"] = [
                    "icon" => "fas fa-user-slash",
                    "link" => "banlist",
                ];
            }

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
