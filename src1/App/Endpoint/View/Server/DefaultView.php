<?php

namespace App\Endpoint\View\Server;

use App\Models\Sets\ServerSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["id","Domain"];
        $table_body = [];
        $server_set = new ServerSet();
        $server_set->loadAll();
        foreach ($server_set as $server) {
            $entry = [];
            $entry[] = $server->getId();
            $entry[] = '<a href="[[SITE_URL]]server/manage/' . $server->getId() . '">' . $server->getDomain() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
