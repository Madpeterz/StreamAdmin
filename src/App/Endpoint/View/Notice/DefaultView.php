<?php

namespace App\Endpoint\View\Notice;

use App\R7\Set\NoticeSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ordering","Name","Object IM","Use bot","Hours remaining"];
        $table_body = [];
        $notice_set = new NoticeSet();
        $notice_set->loadAll();

        foreach ($notice_set as $notice) {
            if ($notice->getHoursRemaining() != 999) {
                $entry = [];
                $entry[] = $notice->getHoursRemaining();
                $entry[] = '<a href="[[url_base]]notice/manage/' . $notice->getId() . '"
                .">' . $notice->getName() . '</a>';
                $entry[] = $this->yesNo[$notice->getSendObjectIM()];
                $entry[] = $this->yesNo[$notice->getUseBot()];
                $entry[] = $notice->getHoursRemaining();
                $table_body[] = $entry;
            }
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
