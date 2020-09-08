<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= " : New";
$template_parts["page_actions"] = "";
$apis = new apis_set();
$apis->loadAll();

$form = new form();
$form->target("server/create");
$form->required(true);
$form->group("Basic config");
$form->col(6);
    $form->text_input("domain","Domain",30,"","ip or uncloudflared proxyed domain/subdomain");
    $form->text_input("controlpanel_url","Control panel",200,"","URL to the control panel");
$form->col(6);
    $form->select("apilink","API / type",0,$apis->get_linked_array("id","name"));
    $form->text_input("api_username","API / Username",200,"","the API username");
    $form->text_input("api_password","API / Password",200,"","the API password");
$form->split();
$form->group("API Flags");
$form->col(5);
    $form->select("opt_password_reset","Opt / PWD reset",1,array(0=>"Disabled",1=>"Allow"));
    $form->select("opt_autodj_next","Opt / ADJ next",1,array(0=>"Disabled",1=>"Allow"));
    $form->select("opt_toggle_autodj","Opt / ADJ toggle",1,array(0=>"Disabled",1=>"Allow"));
$form->col(1);
$form->col(5);
    $form->select("event_enable_start","Event / Enable on start",1,array(0=>"No",1=>"Yes"));
    $form->select("event_disable_expire","Event / Disable on expire",0,array(0=>"No",1=>"Yes"));
    $form->select("event_disable_revoke","Event / Disable on revoke",1,array(0=>"No",1=>"Yes"));
    $form->select("event_reset_password_revoke","Event / Reset password on revoke",1,array(0=>"No",1=>"Yes"));
echo $form->render("Create","primary");
?>
