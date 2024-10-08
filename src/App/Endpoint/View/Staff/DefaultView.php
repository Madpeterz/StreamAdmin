<?php

namespace App\Endpoint\View\Staff;

use App\Models\Sets\StaffSet;

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
        foreach ($staff_set as $staff) {
            $entry = [];
            $entry[] = $staff->getId();
            $username = $staff->getUsername();
            if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
                $username = '<a href="[[SITE_URL]]staff/manage/'
                . $staff->getId() . '">' . $staff->getUsername() . '</a>';
            }
            $entry[] = $username;
            $entry[] = $this->yesNo[$staff->getOwnerLevel()];
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
