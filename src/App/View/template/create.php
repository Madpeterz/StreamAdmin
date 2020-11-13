<?php

$this->output->addSwapTagString("html_title", " ~ Create");
$this->output->addSwapTagString("page_title", " Create new");
$this->output->setSwapTagString("page_actions", "");
$form = new form();
$form->target("template/create");
$form->required(true);
$form->col(3);
    $form->textInput("name", "Name", 30, "", "Name");
$form->split();
$form->col(6);
    $form->textarea("detail", "Template [Object+Bot IM]", 800, "", "Use swap tags as the placeholders! max length 800");
$form->col(6);
    $form->textarea("notecarddetail", "Notecard template", 2000, "", "Use swap tags as the placeholder");
$this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
include "webpanel/view/shared/swaps_table.php";
