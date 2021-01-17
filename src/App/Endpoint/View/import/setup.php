<?php

$this->output->addSwapTagString("html_title", " ~ Setup R4 connection");
$this->setSwapTag("page_title", "R4 DB settings");
$form = new form();
$form->target("import/setconfig");
$form->required(true);
$form->col(6);
    $form->group("DB");
    $form->textInput("db_host", "Host", 999, "localhost", "Host");
    $form->textInput("db_name", "Name", 999, "streamadminr4database", "Database name");
    $form->textInput("db_username", "Username", 999, "dbusername", "Database username");
    $form->textInput("db_pass", "Password", 999, "dbpass", "Database password");
$this->output->addSwapTagString("page_content", $form->render("Setup", "primary"));
