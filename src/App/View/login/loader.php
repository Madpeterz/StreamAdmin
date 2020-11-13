<?php

if ($area == "reset") {
    $this->output->setSwapTagString("html_title", "Reset password");
    $this->output->addSwapTagString("page_content", file_get_contents("theme/" . $site_theme . "/blocks/login/reset.layout"));
} elseif ($area == "resetwithtoken") {
    $this->output->setSwapTagString("html_title", "Recover password");
    $this->output->addSwapTagString("page_content", file_get_contents("theme/" . $site_theme . "/blocks/login/passwordrecover.layout"));
} elseif ($area == "logout") {
    $session->end_session();
    $this->output->redirect("");
} else {
    $this->output->setSwapTagString("html_title", "Login");
    $this->output->addSwapTagString("why_logged_out", $session->get_why_logged_out());
    $this->output->addSwapTagString("page_content", file_get_contents("theme/" . $site_theme . "/blocks/login/login.layout"));
}
