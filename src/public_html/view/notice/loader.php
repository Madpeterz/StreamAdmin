<?php

$stream = new stream();
if ($stream->HasAny() == true) {
    $view_reply->set_swap_tag_string("html_title", "Notices");
    $view_reply->set_swap_tag_string("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Notices");
    $view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]notice/create'><button type='button' class='btn btn-success'>Create</button></a>");
} else {
    $view_reply->redirect("stream?message=Please create a stream first");
}
