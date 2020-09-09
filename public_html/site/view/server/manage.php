<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= " Editing";
$template_parts["page_actions"] = "<a href='[[url_base]]server/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";
$server = new server();
$apis = new apis_set();
$apis->loadAll();
if($server->load($page) == true)
{
    $template_parts["page_title"] .= ":".$server->get_domain()."";
    $form = new form();
    $form->target("server/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->text_input("domain","Domain",30,$server->get_domain(),"ip or uncloudflared proxyed domain/subdomain");
        $form->text_input("controlpanel_url","Control panel",200,$server->get_controlpanel_url(),"URL to the control panel");
    $form->col(6);
        $form->select("apilink","API / type",$server->get_apilink(),$apis->get_linked_array("id","name"));
        $form->text_input("api_url","API / URL",200,$server->get_api_url(),"");
        $form->text_input("api_username","API / Username",200,$server->get_api_username(),"the API username");
        $form->text_input("api_password","API / Password",200,"NoChange","the API password");
    $form->split();
    $form->group("API Flags");
    $form->col(5);
        $form->select("opt_password_reset","Opt / PWD reset",$server->get_opt_password_reset(),array(0=>"Disabled",1=>"Allow"));
        $form->select("opt_autodj_next","Opt / ADJ next",$server->get_opt_autodj_next(),array(0=>"Disabled",1=>"Allow"));
        $form->select("opt_toggle_autodj","Opt / ADJ toggle",$server->get_opt_toggle_autodj(),array(0=>"Disabled",1=>"Allow"));
        $form->select("opt_toggle_status","Opt / Toggle status",$server->get_opt_toggle_status(),array(0=>"Disabled",1=>"Allow"));
    $form->col(1);
    $form->col(5);
        $form->select("event_enable_start","Event / Enable on start",$server->get_event_enable_start(),array(0=>"No",1=>"Yes"));
        $form->select("event_enable_renew","Event / Enable on renewal",$server->get_event_enable_renew(),array(0=>"No",1=>"Yes"));
        $form->select("event_disable_expire","Event / Disable on expire",$server->get_event_disable_expire(),array(0=>"No",1=>"Yes"));
        $form->select("event_disable_revoke","Event / Disable on revoke",$server->get_event_disable_revoke(),array(0=>"No",1=>"Yes"));
        $form->select("event_reset_password_revoke","Event / Reset password on revoke",$server->get_event_reset_password_revoke(),array(0=>"No",1=>"Yes"));
    echo $form->render("Update","primary");
}
else
{
    redirect("server?bubblemessage=unable to find server&bubbletype=warning");
}
?>
