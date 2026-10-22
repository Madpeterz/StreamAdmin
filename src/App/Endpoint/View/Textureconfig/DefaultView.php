<?php

namespace App\Endpoint\View\Textureconfig;

use App\Models\Set\TextureconfigSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Texture packs");
        $table_head = ["id","ID","name"];
        $table_body = [];
        $textureconfig_set = new TextureconfigSet();
        $textureconfig_set->loadAll();

        foreach ($textureconfig_set as $textureconfig) {
            $entry = [];
            $entry[] = $textureconfig->_Id;
            $entry[] = $textureconfig->_Id;
            $entry[] = '<a href="[[SITE_URL]]textureconfig/manage/' . $textureconfig->_Id . '">'
             . $textureconfig->_Name . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
