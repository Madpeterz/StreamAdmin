<?php
$view_reply->add_swap_tag_string("html_title"," ~ Manage");
$view_reply->add_swap_tag_string("page_title"," Editing");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]tree/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>");
$treevender = new treevender();
if($treevender->load($page) == true)
{
    $package_set = new package_set();
    $package_set->loadAll();
    $view_reply->add_swap_tag_string("page_title",":".$treevender->get_name());
    $form = new form();
    $form->target("tree/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->text_input("name","Name",30,$treevender->get_name(),"Name");
    $view_reply->set_swap_tag_string("page_content",$form->render("Update","primary"));
    $view_reply->add_swap_tag_string("page_content","<br/><hr/><br/>");
    $treevender_packages_set = new treevender_packages_set();
    $treevender_packages_set->load_on_field("treevenderlink",$treevender->get_id());
    $table_head = array("ID","Name","Action");
    $table_body = [];
    $used_package_ids = [];
    foreach($treevender_packages_set->get_all_ids() as $treevender_packages_id)
    {
        $treevender_packages = $treevender_packages_set->get_object_by_id($treevender_packages_id);
        $entry = [];
        $package = $package_set->get_object_by_id($treevender_packages->get_packagelink());
        $used_package_ids[] = $package->get_id();
        $entry[] = $treevender_packages->get_id();
        $entry[] = $package->get_name();
        $entry[] = "<a href='[[url_base]]tree/removepackage/".$treevender_packages->get_id()."'><button type='button' class='btn btn-outline-danger btn-sm'>Remove</button></a>";
        $table_body[] = $entry;
    }
    $view_reply->add_swap_tag_string("page_content",render_datatable($table_head,$table_body));
    $unused_index = [];
    foreach($package_set->get_linked_array("id","name") as $id => $name)
    {
        if(in_array($id,$used_package_ids) == false)
        {
            $unused_index[$id] = $name;
        }
    }
    if(count($unused_index) > 0)
    {
        $form = new form();
        $form->target("tree/addpackage/".$page."");
        $form->select("package","Package","",$unused_index);
        $view_reply->add_swap_tag_string("page_content",$form->render("Add package","success"));
    }
}
else
{
    $view_reply->redirect("tree?bubblemessage=unable to find tree vender&bubbletype=warning");
}
?>
