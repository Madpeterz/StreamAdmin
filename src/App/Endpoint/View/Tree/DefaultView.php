<?php

namespace App\Endpoint\View\Tree;

use App\Models\Set\TreevenderSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ID","Name"];
        $table_body = [];
        $treevender_set = new TreevenderSet();
        $treevender_set->loadAll();

        foreach ($treevender_set as $treevender) {
            $entry = [];
            $entry[] = $treevender->getId();
            $entry[] = "[" . $treevender->getId() . '] -> <a href="[[SITE_URL]]tree/manage/'
            . $treevender->getId() . '">' . $treevender->getName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
