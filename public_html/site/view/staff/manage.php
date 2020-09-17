<?php
if($session->get_ownerlevel() == true)
{
    $template_parts["html_title"] .= " ~ Manage";
    $template_parts["page_title"] .= ": Editing staff member";
    $template_parts["page_actions"] = "<a href='[[url_base]]staff/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";

    $staff = new staff();
    if($staff->load_by_field("id",$page) == true)
    {
        $form = new form();
        $form->target("staff/update/".$page."");
        $form->required(true);
        $form->col(6);
            $form->text_input("username","Username",40,$staff->get_username(),"Used to login [does not have to be the same as their SL name]");
            $form->text_input("email","Email",200,$staff->get_email(),"Used to change their password via email");
        echo $form->render("Update","primary");
    }
    else
    {
        redirect("staff?bubblemessage=unable to find staff member&bubbletype=warning");
    }
}
else
{
    redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
?>
