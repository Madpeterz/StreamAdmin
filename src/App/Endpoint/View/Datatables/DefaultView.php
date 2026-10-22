<?php

namespace App\Endpoint\View\Datatables;

use App\Models\Set\DatatableSet;

class DefaultView extends View
{
    public function process(): void
    {
        $DatatablesSet = new DatatableSet();
        $DatatablesSet->limitFields(["name"]);
        $DatatablesSet->loadAll();
        $table_head = ["id","Config name"];
        $table_body = [];
        foreach ($DatatablesSet as $Datatable) {
            $entry = [];
            $entry[] = $Datatable->_Id;
            $entry[] = '<a href="[[SITE_URL]]datatables/manage/' . $Datatable->_Id . '">'
            . $Datatable->_Name . '</a>';
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
