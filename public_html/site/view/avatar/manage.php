<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= "Editing avatar";
$template_parts["page_actions"] = "<a href='[[url_base]]avatar/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";

$avatar = new avatar();
if($avatar->load_by_field("avatar_uid",$page) == true)
{
    $form = new form();
    $form->target("avatar/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->text_input("avatarname","Name",125,$avatar->get_avatarname(),"Madpeter Zond [You can leave out Resident]");
        $form->text_input("avataruuid","SL UUID",3,$avatar->get_avataruuid(),"SecondLife UUID [found on their SL profile]");
    echo $form->render("Update","primary");
}
else
{
    redirect("avatar?bubblemessage=unable to find avatar&bubbletype=warning");
}
?>
