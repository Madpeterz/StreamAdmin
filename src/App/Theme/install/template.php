<?php

$this->output->load_template(
    "install",
    "streamadminr5",
    array("install")
);
$this->output->setSwapTagString("html_menu", "");
$this->output->setSwapTagString("page_title", "");
$this->output->setSwapTagString("page_actions", "");
$this->output->setSwapTagString("page_content", "");
$this->output->setSwapTagString("html_title", "");
