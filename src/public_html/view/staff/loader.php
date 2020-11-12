<?php

$view_reply->set_swap_tag_string("html_title", "Staff");
$view_reply->set_swap_tag_string("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Staff ");
if ($session->get_ownerlevel() == true) {
    $view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]staff/create'><button type='button' class='btn btn-success'>Create</button></a>");
} else {
    $view_reply->set_swap_tag_string("page_actions", "");
}
