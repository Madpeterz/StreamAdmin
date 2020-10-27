<?php
$view_reply->add_swap_tag_string("html_title"," ~ Manage");
$view_reply->add_swap_tag_string("page_title"," Editing");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]notice/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>");
$where_config = array(
    "fields" => array("missing"),
    "values" => array(0),
    "types" => array("i"),
    "matches" => array("=")
);
$notice_notecard_set = new notice_notecard_set();
$notice_notecard_set->load_with_config($where_config);

$notice = new notice();
if($notice->load($page) == true)
{
    if($notice->get_hoursremaining() < 999)
    {
        $current_notecard_notice = new notice_notecard();
        $current_notecard_notice->load($notice->get_notice_notecardlink());
        $view_reply->add_swap_tag_string("page_title",":".$notice->get_name());
        $form = new form();
        $form->target("notice/update/".$page."");
        $form->required(true);
        $form->col(6);
            $form->group("Basic");
            $form->text_input("name","Name",30,$notice->get_name(),"Name");
            $form->textarea("immessage","Message",800,$notice->get_immessage(),"use the swaps as placeholders [max length 800]");
        $form->col(6);
            $form->group("Config");
            $form->select("usebot","Use bot to send IM",$notice->get_usebot(),array(false=>"No",true=>"Yes"));
            $form->number_input("hoursremaining","Hours remain [Trigger at]",$notice->get_hoursremaining(),3,"Max value 998");
        $form->col(12);
            $form->direct_add("<br/>");
        $form->col(6);
            $form->group("Dynamic notecard [Requires bot]");
            $form->select("send_notecard","Enable",$notice->get_send_notecard(),array(false=>"No",true=>"Yes"));
            $form->textarea("notecarddetail","Notecard content",2000,$notice->get_notecarddetail(),"use the swaps as placeholders");
        $form->col(6);
            $form->group("Static notecard");
            $use_notecard_link = $notice->get_notice_notecardlink();
            if(in_array($use_notecard_link,$notice_notecard_set->get_all_ids()) == false)
            {
                $use_notecard_link = 1;
                $form->direct_add("<div class=\"alert alert-danger\" role=\"alert\">Current notecard \"".$current_notecard_notice->get_name()."\" is missing</div>");
            }
            $form->select("notice_notecardlink"," ",$use_notecard_link,$notice_notecard_set->get_linked_array("id","name"));
        $view_reply->set_swap_tag_string("page_content",$form->render("Update","primary"));
        include("webpanel/view/shared/swaps_table.php");
    }
    else
    {
        $view_reply->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
    }
}
else
{
    $view_reply->redirect("notice?bubblemessage=unable to find notice&bubbletype=warning");
}
?>
