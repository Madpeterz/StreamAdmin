<?php

namespace App\View\Outbox;

use App\AvatarSet;
use App\MessageSet;

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
        $avatar_set->loadIds($message_set->getAllByField("avatarlink"));
        foreach ($message_set->getAllIds() as $message_id) {
            $message = $message_set->getObjectByID($message_id);
            $avatar = $avatar_set->getObjectByID($message->getAvatarlink());
            $message_content = $message->getMessage();
            if (strlen($message_content) > 24) {
                $message_content = substr($message_content, 0, 24) . " ...";
            }
            $table_body[] = [$message->getId(),$avatar->getAvatarname(),$message_content];
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
