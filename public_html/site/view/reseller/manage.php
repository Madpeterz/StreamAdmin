<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= "Editing reseller";
$template_parts["page_actions"] = "<a href='[[url_base]]reseller/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";

$avatar = new avatar();
$reseller = new reseller();
if($reseller->load($page) == true)
{
    $avatar->load($reseller->get_avatarlink());
    $template_parts["page_title"] .= ":".$avatar->get_avatarname()."";
    $form = new form();
    $form->target("reseller/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->select("allowed","Allow",$reseller->get_allowed(),array(false=>"No",true=>"Yes"));
        $form->number_input("rate","Rate (as %)",$reseller->get_rate(),3,"Max 100");
    echo $form->render("Update","primary");
}
else
{
    redirect("reseller?bubblemessage=unable to find reseller&bubbletype=warning");
}
?>
