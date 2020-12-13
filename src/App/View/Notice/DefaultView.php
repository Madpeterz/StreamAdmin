<?php

namespace App\View\Login;

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
            if ($notice->getHoursremaining() != 999) {
                $entry = [];
                $entry[] = $notice->getHoursremaining();
                $entry[] = '<a href="[[url_base]]notice/manage/' . $notice->getId() . '"
                .">' . $notice->getName() . '</a>';
                $entry[] = [false => "No",true => "Yes"][$notice->getUsebot()];
                $entry[] = $notice->getHoursremaining();
                $table_body[] = $entry;
            }
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
