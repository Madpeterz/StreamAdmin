<?php
$where_config = array(
    "fields" => array("missing"),
    "values" => array(0),
    "types" => array("i"),
    "matches" => array("=")
);
$notice_notecard_set = new notice_notecard_set();
$notice_notecard_set->load_with_config($where_config);

$view_reply->add_swap_tag_string("html_title"," ~ Create");
$view_reply->add_swap_tag_string("page_title"," : New");
$view_reply->set_swap_tag_string("page_actions","");

$form = new form();
$form->target("notice/create");
$form->required(true);
$form->col(6);
    $form->group("Basic");
    $form->text_input("name","Name",30,"","Name");
    $form->textarea("immessage","Message",800,"","use the swaps as placeholders [max length 800]");
$form->col(6);
    $form->group("Config");
    $form->select("usebot","Use bot to send IM",false,array(false=>"No",true=>"Yes"));
    $form->number_input("hoursremaining","Hours remain [Trigger at]",24,3,"Max value 999");
$form->col(12);
    $form->direct_add("<br/>");
$form->col(6);
    $form->group("Dynamic notecard [Requires bot]");
    $form->select("send_notecard","Enable",false,array(false=>"No",true=>"Yes"));
    $form->textarea("notecarddetail","Notecard content",2000,"","use the swaps as placeholders");
$form->col(6);
    $form->group("Static notecard");
    $form->select("notice_notecardlink"," ",1,$notice_notecard_set->get_linked_array("id","name"));
$view_reply->set_swap_tag_string("page_content",$form->render("Create","primary"));
include("site/view/shared/swaps_table.php");
?>
