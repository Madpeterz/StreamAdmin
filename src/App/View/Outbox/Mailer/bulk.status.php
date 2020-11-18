<?php

namespace App\View\Outbox;

use App\NoticeSet;
use App\Template\Form;

$notice_set = new NoticeSet();
$notice_set->loadAll();


$form = new Form();
$form->target("outbox/bulk/notice");
$form->mode("get");
$form->col(4);
    $form->select("noticelink", "Notice level", 0, $notice_set->getLinkedArray("id", "name"));
$form->col(8);
    $form->textarea("message", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
$pages["Send => Bulk [Notice]"] =  $form->render("Select avatars", "primary")
. "<br/>Send mail to everyone with the selected notice level";
