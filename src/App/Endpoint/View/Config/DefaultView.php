<?php

namespace App\Endpoints\View\Config;

class DefaultView extends View
{
    public function process(): void
    {
        $check_objects = ["App\Server","App\Template","App\Package","App\Stream","App\Slconfig","App\Textureconfig"];
        $all_ok = true;
        foreach ($check_objects as $check) {
            $obj = new $check();
            if ($obj->HasAny() == false) {
                $all_ok = false;
                $this->output->redirect($check);
                break;
            }
        }
        if ($all_ok == true) {
            $config_areas = [
                "Avatars" => "avatar",
                "Template" => "template",
                "System config" => "slconfig",
                "Textures" => "textureconfig",
                "Transactions" => "transactions",
                "Notices" => "notice",
                "Objects" => "objects",
                "Servers" => "server",
            ];
            if ($this->session->getOwnerLevel() == 1) {
                $config_areas["R4 import"] = "import";
                $config_areas["Bot"] = "bot";
                $config_areas["Staff"] = "staff";
                $config_areas["Banlist"] = "banlist";
            }
            $table_head = ["Name"];
            $table_body = [];
            $loop = 0;
            foreach ($config_areas as $key => $value) {
                $entry = [];
                $entry[] = '<a href="[[url_base]]' . $value . '">' . $key . '</a>';
                $table_body[] = $entry;
                $loop++;
            }
            $this->output->addSwapTagString("page_content", render_table($table_head, $table_body));
        }
    }
}
