<?php

$stream = new stream();
if ($stream->HasAny() == true) {
    $this->output->setSwapTagString("html_title", "Texture packs");
    $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]textureconfig/create'><button type='button' class='btn btn-success'>Create</button></a>");
} else {
    $this->output->redirect("stream?message=Please create a stream first");
}
