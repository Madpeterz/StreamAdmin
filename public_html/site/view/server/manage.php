<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= " Editing";
$template_parts["page_actions"] = "<a href='[[url_base]]server/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";
$server = new server();
if($server->load($page) == true)
{
    $template_parts["page_title"] .= ":".$server->get_domain()."";
    $form = new form();
    $form->target("server/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->text_input("domain","Domain",30,$server->get_domain(),"ip or uncloudflared proxyed domain/subdomain");
        $form->text_input("controlpanel_url","Control panel",200,$server->get_controlpanel_url(),"URL to the control panel");
    echo $form->render("Update","primary");
}
else
{
    redirect("server?bubblemessage=unable to find server&bubbletype=warning");
}
?>
