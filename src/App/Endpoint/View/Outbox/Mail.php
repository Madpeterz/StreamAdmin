<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Model\Botconfig;
use App\R7\Set\AvatarSet;
use App\R7\Set\MessageSet;

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
        $avatar_set->loadIds($message_set->getAllByField("avatarLink"));
        $botConfig = new Botconfig();
        $botConfig->loadID(1);
        foreach ($message_set->getAllIds() as $message_id) {
            $message = $message_set->getObjectByID($message_id);
            $avatar = $avatar_set->getObjectByID($message->getAvatarLink());
            $message_content = $message->getMessage();
            if (strlen($message_content) > 24) {
                $message_content = substr($message_content, 0, 24) . " ...";
            }
            $boticon = "";
            if ($avatar->getId() == $botConfig->getAvatarLink()) {
                $boticon = '<i class="fas fa-robot"></i> ';
            }
            $table_body[] = [
                $message->getId(),
                $boticon . $avatar->getAvatarName(),
                $message_content,
            ];
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
