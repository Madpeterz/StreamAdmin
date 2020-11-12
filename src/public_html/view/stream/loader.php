<?php

$package = new package();
$server = new server();
if ($package->HasAny() == true) {
    if ($server->HasAny() == true) {
        $view_reply->set_swap_tag_string("html_title", "Streams");
        $view_reply->set_swap_tag_string("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]stream/create'><button type='button' class='btn btn-success'>Create</button></a>");
    } else {
        $view_reply->redirect("server?message=Please create a server before creating a stream");
    }
} else {
    $view_reply->redirect("package?message=Please create a package before creating a stream");
}
