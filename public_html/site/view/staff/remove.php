<?php
if($session->get_ownerlevel() == true)
{
    $template_parts["html_title"] .= " ~ Remove";
    $template_parts["page_title"] .= "Remove staff member: ";
    $template_parts["page_title"] .= $page;
    $template_parts["page_actions"] = "";

    $form = new form();
    $form->target("staff/remove/".$page."");
    $form->required(true);
    $form->col(6);
    $form->group("Warning</h4><p>The web interface will not allow you to remove owner level accounts!</p><h4>");
    $form->text_input("accept","Type \"Accept\"",30,"","This will delete the staff member");
    echo $form->render("Remove","danger");
    echo "";
}
else
{
    redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
?>
