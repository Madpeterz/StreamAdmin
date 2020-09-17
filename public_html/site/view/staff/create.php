<?php
if($session->get_ownerlevel() == true)
{
    $template_parts["html_title"] .= " ~ Create";
    $template_parts["page_title"] .= "Create new staff account";
    $template_parts["page_actions"] = "";
    $form = new form();
    $form->target("staff/create");
    $form->required(true);
    $form->col(6);
        $form->text_input("avataruid","Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>",8,"","Avatar uid");
        $form->text_input("username","Username",40,null,"Used to login [does not have to be the same as their SL name]");
        $form->text_input("email","Email",200,"","Used to change their password via email");
    print $form->render("Create","primary");
    print "<br/><p>Once created they can use the Reset password system to gain access</p>";
}
else
{
    redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
?>
