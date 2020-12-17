<?php

namespace App\Endpoints\View\Staff;

use App\Models\StaffSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ List");
        $this->output->addSwapTagString("page_title", ": List");

        $staff_set = new StaffSet();
        $staff_set->loadAll();
        $table_head = ["id","Username","Owner"];
        $table_body = [];
        foreach ($staff_set->getAllIds() as $staff_id) {
            $staff = $staff_set->getObjectByID($staff_id);
            $entry = [];
            $entry[] = $staff->getId();
            $username = $staff->getUsername();
            if ($this->session->getOwnerLevel() == true) {
                $username = '<a href="[[url_base]]staff/manage/'
                . $staff->getId() . '">' . $staff->getUsername() . '</a>';
            }
            $entry[] = $username;
            $entry[] = $this->yesNo[$staff->getOwnerLevel()];
            $table_body[] = $entry;
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
