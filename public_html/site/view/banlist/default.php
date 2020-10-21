<?php
if($session->get_ownerlevel() == 1)
{
    $match_with = "newest";
    $input = new inputFilter();
    $name = $input->postFilter("name");
    $uuid = $input->postFilter("uuid");
    $wherefields = array();
    $wherevalues = array();
    $wheretypes = array();
    $wherematchs = array();
    if(strlen($uuid) == 36)
    {
        $match_with = "uuid";
        $wherefields = array("avataruuid");
        $wherevalues = array($uuid);
        $wheretypes = array("s");
        $wherematchs = array("=");
    }
    else if(strlen($name) >= 2)
    {
        $match_with = "name";
        $wherefields = array("avatarname");
        $wherevalues = array($name);
        $wheretypes = array("s");
        $wherematchs = array("% LIKE %");
    }
    $banlist_set = new banlist_set();
    $avatar_set = new avatar_set();
    if($match_with == "newest")
    {
        $banlist_set->load_newest(30);
        $avatar_set->load_ids($banlist_set->get_unique_array("avatar_link"));
        $template_parts["page_title"] .= "Newest 30 avatars banned";
    }
    else
    {
        $where_config = array(
            "fields" => $wherefields,
            "values" => $wherevalues,
            "types" => $wheretypes,
            "matches" => $wherematchs
        );
        $avatar_set->load_with_config($where_config);
        if($match_with == "name") $template_parts["page_title"] .= "Names containing: ".$name."";
        else $template_parts["page_title"] .= "UUID: ".$uuid."";
        $banlist_set->load_ids($avatar_set->get_all_ids(),"avatar_link");
    }

    $table_head = array("id","Name","Remove");
    $table_body = array();

    foreach($banlist_set->get_all_ids() as $ban_id)
    {
        $banlist = $banlist_set->get_object_by_id($ban_id);
        $avatar = $avatar_set->get_object_by_id($banlist->get_avatar_link());

        $entry = array();
        $entry[] = $banlist->get_id();
        $form = new form();
        $form->target("banlist/clear/'.$ban_id.'");
        $form->required(true);
        $entry[] = $avatar->get_avatarname();
        $entry[] = $form->render("Remove","danger");
        $table_body[] = $entry;
    }
    $view_reply->set_swap_tag_string("page_content",render_datatable($table_head,$table_body));
    $view_reply->add_swap_tag_string("page_content","<br/><hr/>");
    $form = new form();
    $form->mode("get");
    $form->target("banlist");
    $form->required(false);
    $form->col(4);
        $form->group("Search: Name or UUID");
        $form->text_input("name","Name",30,"","2 letters min to match");
        $form->text_input("uuid","SL UUID",36,"","a full UUID to match");
    $form1 = $form->render("Start","info");
    $form = new form();
    $form->target("banlist/create");
    $form->required(true);
    $form->col(4);
        $form->group("Add to ban list");
        $form->text_input("uid","Avatar UID",30,"","you can find it in avatars area, Name can be used for existing avatars.");
    $form2 = $form->render("Goodbye","primary");
    $mygrid = new grid();
    $mygrid->add_content($form1,6);
    $mygrid->add_content($form2,6);
    $view_reply->add_swap_tag_string("page_content",$mygrid->get_output());
}
?>
