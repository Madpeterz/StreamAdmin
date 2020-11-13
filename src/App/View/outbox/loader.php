<?php

$stream = new stream();
if ($stream->HasAny() == true) {
    $this->output->setSwapTagString("html_title", "Outbox");
    $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $this->output->setSwapTagString("page_actions", "");
} else {
    $this->output->redirect("stream?message=Please create a stream first");
}
