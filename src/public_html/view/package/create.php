<?php

$view_reply->add_swap_tag_string("html_title", " ~ Create");
$view_reply->add_swap_tag_string("page_title", " Create new package");
$view_reply->set_swap_tag_string("page_actions", "");

$template_set = new template_set();
$template_set->loadAll();
$servertypes_set = new servertypes_set();
$servertypes_set->loadAll();

$form = new form();
$form->target("package/create");
$form->required(true);
$form->col(6);
    $form->group("Basics");
    $form->text_input("name", "Name", 30, "", "Package name [60 chars]");
    $form->select("templatelink", "Template", 0, $template_set->get_linked_array("id", "name"));
    $form->select("servertypelink", "Server type", 1, $servertypes_set->get_linked_array("id", "name"));
    $form->text_input("api_template", "API template", 50, "", "API template name");
$form->col(6);
    $form->group("Terms");
    $form->number_input("cost", "Cost L$", null, 5, "Max L$ 99999");
    $form->number_input("days", "Days per cost", null, 3, "Max 999 days");
    $form->number_input("listeners", "Listeners", null, 3, "Max listeners 999");
    $form->number_input("bitrate", "Bitrate", null, 3, "Max kbps 999");
$form->split();
$form->col(6);
    $form->group("Textures");
    $form->uuid_input("texture_uuid_soldout", "Sold out", "", "UUID of texture");
    $form->uuid_input("texture_uuid_instock_small", "In stock [Small]", "", "UUID of texture");
    $form->uuid_input("texture_uuid_instock_selected", "In stock [Selected]", "", "UUID of texture");
$form->col(6);
    $form->group("Auto DJ");
    $form->select("autodj", "Enabled", false, array(false => "No",true => "Yes"));
    $form->number_input("autodj_size", "Storage GB", null, 3, "Max GB storage 9999");
$view_reply->set_swap_tag_string("page_content", $form->render("Create", "primary"));
