<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Botconfig;
use App\Models\Set\NotecardmailSet;
use App\Models\Set\NoticenotecardSet;

class Docs extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent static doc notecards");
        $table_head = ["id","Avatar name","Notecard"];
        $table_body = [];
        $notecardmail = new NotecardmailSet();
        $notecardmail->loadAll();
        $noticenotecards = $notecardmail->relatedNoticenotecard();
        $avatar_set = $notecardmail->relatedAvatar();
        $botConfig = new Botconfig();
        $botConfig->loadID(1);
        foreach ($notecardmail as $staticnotecard) {
            $avatar = $avatar_set->getObjectByID($staticnotecard->_AvatarLink);
            $notecard = $noticenotecards->getObjectByID($staticnotecard->_NoticeNotecardLink);
            $entry = [];
            $entry[] = $staticnotecard->_Id;
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->_AvatarName . '">'
            . $avatar->_AvatarName . '</a>';
            $entry[] = $notecard->_Name;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
