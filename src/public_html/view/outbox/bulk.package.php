<?php

$package_set = new package_set();
$package_set->loadAll();


$form = new form();
$form->target("outbox/bulk/package");
$form->mode("get");
$form->col(4);
    $form->select("packagelink", "Package", 0, $package_set->get_linked_array("id", "name"));
$form->col(8);
    $form->textarea("message", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [Package]"] =  $form->render("Select avatars", "primary") . "<br/>Send mail to everyone with the selected package";
