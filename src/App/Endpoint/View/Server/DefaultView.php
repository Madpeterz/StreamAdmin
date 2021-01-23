<?php

namespace App\Endpoint\View\Server;

use App\Models\ApisSet;
use App\Models\ServerSet;
use App\Template\Form;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["id","Domain"];
        $table_body = [];
        $server_set = new ServerSet();
        $server_set->loadAll();
        $apis_set = new ApisSet();
        $apis_set->loadAll();
        $has_api_sync = false;
        foreach ($server_set->getAllIds() as $server_id) {
            $server = $server_set->getObjectByID($server_id);
            if ($server->getApiSyncAccounts() == true) {
                $has_api_sync = true;
                $table_head = ["id","Domain","Last sync","Sync"];
                break;
            }
        }
        foreach ($server_set->getAllIds() as $server_id) {
            $server = $server_set->getObjectByID($server_id);
            $api = $apis_set->getObjectByID($server->getApiLink());
            $entry = [];
            $entry[] = $server->getId();
            $entry[] = '<a href="[[url_base]]server/manage/' . $server->getId() . '">' . $server->getDomain() . '</a>';
            if ($has_api_sync == true) {
                if (($server->getApiSyncAccounts() == true) && ($api->getApiSyncAccounts() == true)) {
                    $form = new Form();
                    $form->target("server/SyncAccounts/" . $server->getId() . "");
                    $entry[] = expiredAgo($server->getLastApiSync());
                    $entry[] = $form->render("Sync", "primary", true, true);
                } else {
                    $entry[] = " - ";
                    $entry[] = " - ";
                }
            }
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
