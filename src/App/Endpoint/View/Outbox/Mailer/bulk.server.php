<?php

namespace App\Endpoint\View\Outbox;

use App\Models\ServerSet;
use App\Template\Form;

$server_set = new ServerSet();
$server_set->loadAll();


$form = new Form();
$form->target("outbox/bulk/server");
$form->mode("get");
$form->col(4);
    $form->select("serverLink", "Server", 0, $server_set->getLinkedArray("id", "domain"));
$form->col(8);
    $form->textarea("message", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [Server]"] =  $form->render("Select avatars", "primary")
. "<br/>Send mail to everyone with the selected server";
