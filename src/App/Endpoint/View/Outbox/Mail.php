<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Botconfig;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\MessageSet;

class Mail extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent mail");
        $table_head = ["id","Avatar name","Start of message"];
        $table_body = [];
        $message_set = new MessageSet();
        $message_set->loadAll();
        $avatar_set = new AvatarSet();
        $avatar_set->loadByValues($message_set->getAllByField("avatarLink"));
        $botConfig = new Botconfig();
        $botConfig->loadID(1);
        foreach ($message_set as $message) {
            $avatar = $avatar_set->getObjectByID($message->getAvatarLink());
            $message_content = $message->getMessage();
            if (strlen($message_content) > 24) {
                $message_content = substr($message_content, 0, 24) . " ...";
            }
            $entry = [];
            $entry[] = $message->getId();
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">
            ' . $avatar->getAvatarName() . '</a>';
            $entry[] = $message_content;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
