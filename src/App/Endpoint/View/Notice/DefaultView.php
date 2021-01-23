<?php

namespace App\Endpoint\View\Login;

use App\Models\NoticeSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = ["ordering","Name","Use bot","Hours remaining"];
        $table_body = [];
        $notice_set = new NoticeSet();
        $notice_set->loadAll();

        foreach ($notice_set->getAllIds() as $notice_id) {
            $notice = $notice_set->getObjectByID($notice_id);
            if ($notice->getHoursRemaining() != 999) {
                $entry = [];
                $entry[] = $notice->getHoursRemaining();
                $entry[] = '<a href="[[url_base]]notice/manage/' . $notice->getId() . '"
                .">' . $notice->getName() . '</a>';
                $entry[] = [false => "No",true => "Yes"][$notice->getUseBot()];
                $entry[] = $notice->getHoursRemaining();
                $table_body[] = $entry;
            }
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
