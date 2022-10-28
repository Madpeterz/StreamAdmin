<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Sets\BotcommandqSet;

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
            $entry[] = $botcommand->getId();
            $entry[] = $botcommand->getCommand();
            $commandsCount = 0;
            if ($botcommand->getArgs() != null) {
                $commandsCount = count(json_decode($botcommand->getArgs()));
            }
            $entry[] = $commandsCount;
            $entry[] = date('d/m/Y @ G:i:s', $botcommand->getUnixtime());
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
