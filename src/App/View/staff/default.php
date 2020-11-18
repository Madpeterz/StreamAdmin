<?php

$this->output->addSwapTagString("html_title", " ~ List");
$this->output->addSwapTagString("page_title", ": List");

$staff_set = new staff_set();
$staff_set->loadAll();
$table_head = ["id","Username","Owner"];
$table_body = [];
foreach ($staff_set->getAllIds() as $staff_id) {
    $staff = $staff_set->getObjectByID($staff_id);
    $entry = [];
    $entry[] = $staff->getId();
    if ($session->get_ownerlevel() == true) {
        $entry[] = '<a href="[[url_base]]staff/manage/' . $staff->getId() . '">' . $staff->get_username() . '</a>';
    } else {
        $entry[] = $staff->get_username();
    }
    $entry[] = [false => "No",true => "Yes"][$staff->get_ownerlevel()];
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
