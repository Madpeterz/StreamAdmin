<?php

$this->output->load_template(
    "sidemenu",
    "streamadminr5",
    array("topper","header","body_start","left_content","center_content","body_end","modals","footer")
);
$this->output->setSwapTagString("html_menu", "");
$this->output->setSwapTagString("page_title", "");
$this->output->setSwapTagString("page_actions", "");
$this->output->setSwapTagString("page_content", "");
$this->output->setSwapTagString("html_title", "");
$this->output->setSwapTagString("html_js_onready", "");
