<?php

namespace App\Endpoint\View\Client;

use App\Models\NoticeSet;
use App\Models\Rental;

class SelectNoticeLevel extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", "Select a notice level");
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $rental = new Rental();
        $group_count = $this->sql->groupCountV2($rental->getTable(), "noticeLink");
        $table_head = ["id","NoticeLevel","Count"];
        $table_body = [];
        if ($group_count["status"] == true) {
            foreach ($group_count["dataset"] as $countentry) {
                $notice = $notice_set->getObjectByID($countentry["noticeLink"]);
                $entry = [];
                $entry[] = $notice->getId();
                $entry[] = '<a href="[[url_base]]client/bynoticelevel/' . $notice->getId() . '">'
                 . $notice->getName() . '</a>';
                $entry[] = $countentry["Entrys"];
                $table_body[] = $entry;
            }
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
