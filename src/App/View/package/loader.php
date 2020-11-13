<?php

$template = new template();
if ($template->HasAny() == true) {
    $this->output->setSwapTagString("html_title", "Packages");
    $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]package/create'><button type='button' class='btn btn-success'>Create</button></a>");
} else {
    $this->output->redirect("template?message=Please create a template before creating a package");
}
