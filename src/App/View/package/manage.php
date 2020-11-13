<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing package");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]package/remove/" . $page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

$template_set = new template_set();
$template_set->loadAll();
$servertypes_set = new servertypes_set();
$servertypes_set->loadAll();

$package = new package();
if ($package->load_by_field("package_uid", $page) == true) {
    $template_parts["page_title"] .= ":" . $package->get_name() . "";
    $form = new form();
    $form->target("package/update/" . $page . "");
    $form->required(true);
    $form->col(6);
        $form->group("Basics");
        $form->textInput("name", "Name", 30, $package->get_name(), "Package name [60 chars]");
        $form->select("templatelink", "Template", $package->get_templatelink(), $template_set->get_linked_array("id", "name"));
        $form->select("servertypelink", "Server type", $package->get_servertypelink(), $servertypes_set->get_linked_array("id", "name"));
        $form->textInput("api_template", "API template", 50, $package->get_api_template(), "API template name");
    $form->col(6);
        $form->group("Terms");
        $form->numberInput("cost", "Cost L$", $package->get_cost(), 5, "Max L$ 99999");
        $form->numberInput("days", "Days per cost", $package->get_days(), 3, "Max 999 days");
        $form->numberInput("listeners", "Listeners", $package->get_listeners(), 3, "Max listeners 999");
        $form->numberInput("bitrate", "Bitrate", $package->get_bitrate(), 3, "Max kbps 999");
    $form->split();
    $form->col(6);
        $form->group("Textures");
        $form->texture_input("texture_uuid_soldout", "Sold out", 36, $package->get_texture_uuid_soldout(), "UUID of texture");
        $form->texture_input("texture_uuid_instock_small", "In stock [Small]", 36, $package->get_texture_uuid_instock_small(), "UUID of texture");
        $form->texture_input("texture_uuid_instock_selected", "In stock [Selected]", 36, $package->get_texture_uuid_instock_selected(), "UUID of texture");
    $form->col(6);
        $form->group("Auto DJ");
        $form->select("autodj", "Enabled", $package->get_autodj(), array(false => "No",true => "Yes"));
        $form->numberInput("autodj_size", "Storage GB", $package->get_autodj_size(), 3, "Max GB storage 9999");
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
} else {
    $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
}
