<?php

namespace App\Endpoint\View\Tree;

use App\R7\Set\TreevenderSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ID","TreeID","Name"];
        $table_body = [];
        $treevender_set = new TreevenderSet();
        $treevender_set->loadAll();

        foreach ($treevender_set->getAllIds() as $treevender_id) {
            $treevender = $treevender_set->getObjectByID($treevender_id);
            $entry = [];
            $entry[] = $treevender->getId();
            $entry[] = $treevender->getId();
            $entry[] = '<a href="[[url_base]]tree/manage/'
            . $treevender->getId() . '">' . $treevender->getName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
