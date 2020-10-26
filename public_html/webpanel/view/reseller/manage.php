<?php
$view_reply->add_swap_tag_string("html_title"," ~ Manage");
$view_reply->add_swap_tag_string("page_title"," Editing reseller");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]reseller/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>");

$avatar = new avatar();
$reseller = new reseller();
if($reseller->load($page) == true)
{
    $avatar->load($reseller->get_avatarlink());
    $view_reply->add_swap_tag_string("page_title",":".$avatar->get_avatarname());
    $form = new form();
    $form->target("reseller/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->select("allowed","Allow",$reseller->get_allowed(),array(false=>"No",true=>"Yes"));
        $form->number_input("rate","Rate (as %)",$reseller->get_rate(),3,"Max 100");
    $view_reply->set_swap_tag_string("page_content",$form->render("Update","primary"));
}
else
{
    $view_reply->redirect("reseller?bubblemessage=unable to find reseller&bubbletype=warning");
}
?>
