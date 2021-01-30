<?php

namespace App\Endpoint\View\Import;

use App\R7\Model\Server;
use App\R4\ItemsSet;

class Servers extends View
{
    public function process(): void
    {
        $r4_items = new ItemsSet();
        $r4_items->reconnectSql($this->oldSqlDB);
        $r4_items->loadAll();

        $r4_items_servers = $r4_items->getUniqueArray("streamurl");

        $all_ok = true;
        $created_servers = 0;
        foreach ($r4_items_servers as $serverurl) {
            $server = new Server();
            $server->setDomain($serverurl);
            $server->setControlPanelURL($serverurl);
            $server_status = $server->createEntry();
            if ($server_status["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create server because: " . $server_status["message"]
                );
                $all_ok = false;
                break;
            }
            $created_servers++;
        }
        if ($all_ok == false) {
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $created_servers . " servers <br/> <a href=\"[[url_base]]import\">Back to menu</a>"
        );
    }
}
