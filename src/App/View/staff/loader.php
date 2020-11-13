<?php

$this->output->setSwapTagString("html_title", "Staff");
$this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Staff ");
if ($session->get_ownerlevel() == true) {
    $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]staff/create'><button type='button' class='btn btn-success'>Create</button></a>");
} else {
    $this->output->setSwapTagString("page_actions", "");
}
