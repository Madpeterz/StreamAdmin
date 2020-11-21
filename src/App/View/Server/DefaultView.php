<?php

namespace App\View\Server;

use App\ApisSet;
use App\ServerSet;
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
            if ($server->getApi_sync_accounts() == true) {
                $has_api_sync = true;
                $table_head = ["id","Domain","Last sync","Sync"];
                break;
            }
        }
        foreach ($server_set->getAllIds() as $server_id) {
            $server = $server_set->getObjectByID($server_id);
            $api = $apis_set->getObjectByID($server->getApilink());
            $entry = [];
            $entry[] = $server->getId();
            $entry[] = '<a href="[[url_base]]server/manage/' . $server->getId() . '">' . $server->getDomain() . '</a>';
            if ($has_api_sync == true) {
                if (($server->getApi_sync_accounts() == true) && ($api->getApi_sync_accounts() == true)) {
                    $form = new Form();
                    $form->target("server/sync_accounts/" . $server->getId() . "");
                    $entry[] = expired_ago($server->getLast_api_sync());
                    $entry[] = $form->render("Sync", "primary", true, true);
                } else {
                    $entry[] = " - ";
                    $entry[] = " - ";
                }
            }
            $table_body[] = $entry;
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
