<?php

$this->output->addSwapTagString(
    "html_js_onready",
    file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Server/js_on_select_api.js")
);
