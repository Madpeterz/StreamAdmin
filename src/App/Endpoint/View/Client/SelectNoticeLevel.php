<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\NoticeSet;
use App\Models\Rental;
use App\Models\Set\RentalSet;

class SelectNoticeLevel extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", "Select a notice level");
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $rental = new Rental();
        $group_count = $this->siteConfig->getSQL()->groupCountV2($rental->getTable(), "noticeLink");
        $table_head = ["id","NoticeLevel","Count"];
        $table_body = [];
        if ($group_count->status == true) {
            foreach ($group_count->dataset as $countentry) {
                $notice = $notice_set->getObjectByID($countentry["noticeLink"]);
                $entry = [];
                $entry[] = $notice->getId();
                $entry[] = '<a href="[[SITE_URL]]client/bynoticelevel/' . $notice->getId() . '">'
                 . $notice->getName() . '</a>';
                $entry[] = $countentry["items"];
                $table_body[] = $entry;
            }
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
