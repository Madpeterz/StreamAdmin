<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= "Create new texture config pack";
$template_parts["page_actions"] = "";
$form = new form();
$form->target("textureconfig/create");
$form->required(true);
$form->col(6);
    $form->text_input("name","Name",30,"","Name");
    $form->text_input("getting_details","Fetching details",36,"","UUID of texture");
    $form->text_input("request_details","Request details",36,"","UUID of texture");
$form->split();
$form->col(6);
    $form->text_input("offline","Offline",36,"","UUID of texture");
    $form->text_input("wait_owner","Waiting for owner",36,"","UUID of texture");
    $form->text_input("inuse","Inuse",36,"","UUID of texture");
    $form->text_input("treevend_waiting","Tree vend [Wait]",36,"","UUID of texture");
$form->col(6);
    $form->text_input("make_payment","Request payment",36,"","UUID of texture");
    $form->text_input("stock_levels","Stock levels",36,"","UUID of texture");
    $form->text_input("renew_here","Renew here",36,"","UUID of texture");
    $form->text_input("proxyrenew","Proxy Renew",36,"","UUID of texture");
echo $form->render("Create","primary");
?>
