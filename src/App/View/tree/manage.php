<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]tree/remove/" . $this->page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
$treevender = new treevender();
if ($treevender->load($this->page) == true) {
    $package_set = new package_set();
    $package_set->loadAll();
    $this->output->addSwapTagString("page_title", ":" . $treevender->getName());
    $form = new form();
    $form->target("tree/update/" . $this->page . "");
    $form->required(true);
    $form->col(6);
        $form->textInput("name", "Name", 30, $treevender->getName(), "Name");
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    $this->output->addSwapTagString("page_content", "<br/><hr/><br/>");
    $treevender_packages_set = new treevender_packages_set();
    $treevender_packages_set->load_on_field("treevenderlink", $treevender->getId());
    $table_head = ["ID","Name","Action"];
    $table_body = [];
    $used_package_ids = [];
    foreach ($treevender_packages_set->getAllIds() as $treevender_packages_id) {
        $treevender_packages = $treevender_packages_set->getObjectByID($treevender_packages_id);
        $entry = [];
        $package = $package_set->getObjectByID($treevender_packages->get_packagelink());
        $used_package_ids[] = $package->getId();
        $entry[] = $treevender_packages->getId();
        $entry[] = $package->getName();
        $entry[] = "<a href='[[url_base]]tree/removepackage/" . $treevender_packages->getId() . "'><button type='button' class='btn btn-outline-danger btn-sm'>Remove</button></a>";
        $table_body[] = $entry;
    }
    $this->output->addSwapTagString("page_content", render_datatable($table_head, $table_body));
    $unused_index = [];
    foreach ($package_set->getLinkedArray("id", "name") as $id => $name) {
        if (in_array($id, $used_package_ids) == false) {
            $unused_index[$id] = $name;
        }
    }
    if (count($unused_index) > 0) {
        $form = new form();
        $form->target("tree/addpackage/" . $this->page . "");
        $form->select("package", "Package", "", $unused_index);
        $this->output->addSwapTagString("page_content", $form->render("Add package", "success"));
    }
} else {
    $this->output->redirect("tree?bubblemessage=unable to find tree vender&bubbletype=warning");
}
