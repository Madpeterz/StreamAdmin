<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Set\BotcommandqSet;

class Bot extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent bot commands");
        $table_head = ["id","Command","Args [count]","Datetime"];
        $table_body = [];
        $botcommandsQ = new BotcommandqSet();
        $botcommandsQ->loadAll();
        foreach ($botcommandsQ as $botcommand) {
            $entry = [];
            $entry[] = $botcommand->_Id;
            $entry[] = $botcommand->_Command;
            $commandsCount = 0;
            if ($botcommand->_Args != null) {
                $commandsCount = count(json_decode($botcommand->_Args));
            }
            $entry[] = $commandsCount;
            $entry[] = date('d/m/Y @ G:i:s', $botcommand->_Unixtime);
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
