<?php
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
$avatar_set = new avatar_set();
if($match_with == "newest")
{
    $avatar_set->load_newest(30);
    $view_reply->set_swap_tag_string("page_title","Newest 30 avatars");
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

    if($match_with == "name") $view_reply->set_swap_tag_string("page_title","Names containing: ".$name);
    else $view_reply->set_swap_tag_string("page_title","UUID: ".$uuid);
}
$table_head = array("id","UID","Name");
$table_body = array();

foreach($avatar_set->get_all_ids() as $avatar_id)
{
    $avatar = $avatar_set->get_object_by_id($avatar_id);
    $entry = array();
    $entry[] = $avatar->get_id();
    $entry[] = '<a href="[[url_base]]avatar/manage/'.$avatar->get_avatar_uid().'">'.$avatar->get_avatar_uid().'</a>';
    $entry[] = $avatar->get_avatarname();
    $table_body[] = $entry;
}
$view_reply->add_swap_tag_string("html_title","~ ".$view_reply->get_swap_tag_string("page_title"));
$view_reply->add_swap_tag_string("page_content",render_datatable($table_head,$table_body));
$view_reply->set_swap_tag_string("page_content","<br/><hr/>");

$form = new form();
$form->mode("get");
$form->target("avatar");
$form->required(false);
$form->col(4);
    $form->group("Search: Name or UUID");
    $form->text_input("name","Name",30,"","2 letters min to match");
    $form->text_input("uuid","SL UUID",36,"","a full UUID to match");
$view_reply->add_swap_tag_string("page_content",$form->render("Start","info"));
?>
