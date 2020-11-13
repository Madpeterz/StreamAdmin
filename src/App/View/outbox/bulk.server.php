<?php

$server_set = new server_set();
$server_set->loadAll();


$form = new form();
$form->target("outbox/bulk/server");
$form->mode("get");
$form->col(4);
    $form->select("serverlink", "Server", 0, $server_set->get_linked_array("id", "domain"));
$form->col(8);
    $form->textarea("message", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [Server]"] =  $form->render("Select avatars", "primary") . "<br/>Send mail to everyone with the selected server";
