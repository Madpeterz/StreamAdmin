<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Sets\PackageSet;
use YAPF\Bootstrap\Template\Form;

$package_set = new PackageSet();
$package_set->loadAll();


$form = new Form();
$form->target("outbox/bulk/clients");
$form->mode("get");
$form->col(8);
    $form->textarea("messageClients", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [All Clients]"] =  $form->render("Select avatars", "primary");
