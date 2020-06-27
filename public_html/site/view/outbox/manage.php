<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= " Editing";
$template_parts["page_actions"] = "<a href='[[url_base]]notice/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";
$notice = new notice();
if($notice->load($page) == true)
{
    if($notice->get_hoursremaining() < 999)
    {
        $template_parts["page_title"] .= ":".$notice->get_name()."";
        $form = new form();
        $form->target("notice/update/".$page."");
        $form->required(true);
        $form->col(6);
            $form->group("Basic");
            $form->text_input("name","Name",30,$notice->get_name(),"Name");
            $form->textarea("immessage","Message",800,$notice->get_immessage(),"use the swaps as placeholders");
        $form->col(6);
            $form->group("Config");
            $form->select("usebot","Use bot to send IM",$notice->get_usebot(),array(false=>"No",true=>"Yes"));
            $form->number_input("hoursremaining","Hours remain [Trigger at]",$notice->get_hoursremaining(),3,"Max value 998");
        echo $form->render("Update","primary");
        include("site/view/shared/swaps_table.php");
    }
    else
    {
        redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
    }
}
else
{
    redirect("notice?bubblemessage=unable to find notice&bubbletype=warning");
}
?>
