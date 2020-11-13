<?php

$package = new package();
$server = new server();
if ($package->HasAny() == true) {
    if ($server->HasAny() == true) {
        $this->output->setSwapTagString("html_title", "Streams");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]stream/create'><button type='button' class='btn btn-success'>Create</button></a>");
    } else {
        $this->output->redirect("server?message=Please create a server before creating a stream");
    }
} else {
    $this->output->redirect("package?message=Please create a package before creating a stream");
}
