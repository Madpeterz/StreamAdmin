<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Set\PackageSet;
use App\Template\Form;

$package_set = new PackageSet();
$package_set->loadAll();


$form = new Form();
$form->target("outbox/bulk/package");
$form->mode("get");
$form->col(4);
    $form->select("packageLink", "Package", 0, $package_set->getLinkedArray("id", "name"));
$form->col(8);
    $form->textarea("messagePackage", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [Package]"] =  $form->render("Select avatars", "primary")
. "<br/>Send mail to everyone with the selected package";
