<?php

namespace App\Endpoint\View\Tree;

use App\Models\TreevenderSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ID","ID - Name"];
        $table_body = [];
        $treevender_set = new TreevenderSet();
        $treevender_set->loadAll();

        foreach ($treevender_set->getAllIds() as $treevender_id) {
            $treevender = $treevender_set->getObjectByID($treevender_id);
            $entry = [];
            $entry[] = $treevender->getId();
            $entry[] = '' . $treevender->getId() . ' - <a href="[[url_base]]tree/manage/'
            . $treevender->getId() . '">' . $treevender->getName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", render_datatable($table_head, $table_body));
    }
}
