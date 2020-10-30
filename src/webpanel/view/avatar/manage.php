<?php
$view_reply->add_swap_tag_string("html_title","~ Manage");
$view_reply->set_swap_tag_string("page_title","Editing avatar");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]avatar/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>");

$avatar = new avatar();
if($avatar->load_by_field("avatar_uid",$page) == true)
{
    $form = new form();
    $form->target("avatar/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->text_input("avatarname","Name",125,$avatar->get_avatarname(),"Madpeter Zond [You can leave out Resident]");
        $form->text_input("avataruuid","SL UUID",3,$avatar->get_avataruuid(),"SecondLife UUID [found on their SL profile]");
    $view_reply->set_swap_tag_string("page_content",$form->render("Update","primary"));
}
else
{
    $view_reply->redirect("avatar?bubblemessage=unable to find avatar&bubbletype=warning");
}
?>
