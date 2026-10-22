<?php

namespace App\Endpoint\View\Notice;

use App\Models\Set\NoticeSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ordering","Name","Object IM","Use bot","Hours remaining"];
        $table_body = [];
        $notice_set = new NoticeSet();
        $notice_set->loadAll();

        foreach ($notice_set as $notice) {
            if ($notice->_HoursRemaining == 999) {
                continue;
            }
            $entry = [];
            $entry[] = $notice->_HoursRemaining;
            $entry[] = '<a href="[[SITE_URL]]notice/manage/' . $notice->_Id . '"
            .">' . $notice->_Name . '</a>';
            $entry[] = $this->yesNo[$notice->_SendObjectIM];
            $entry[] = $this->yesNo[$notice->_UseBot];
            $entry[] = $notice->_HoursRemaining;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
