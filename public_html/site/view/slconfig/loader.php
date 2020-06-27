<?php
$template_parts["html_title"] = " System setup";
$template_parts["page_title"] = "Editing system setup";
$template_parts["page_actions"] = "";
$slconfig = new slconfig();
$slconfig->load(1);
$avatar = new avatar();
$avatar->load($slconfig->get_owner_av());

$form = new form();
$form->target("slconfig/update/".$page."");
$form->required(true);
$form->col(6);
    $form->group("Core");
    $form->direct_add("Current owner: ".$avatar->get_avatarname()."<br/>");
    $form->text_input("owneravuid","Owner avatar UID <a href=\"[[url_base]]avatar\" target=\"_blank\">Find</a>",8,$avatar->get_avatar_uid(),"Not a SL uuid!");
    $form->text_input("sllinkcode","Link code [SL->Server]",30,$slconfig->get_sllinkcode(),"The code shared by your vendors to connet");
    $form->text_input("publiclinkcode","Public Link code [SL->Server]",30,$slconfig->get_publiclinkcode(),"The code shared by your user hud");
    $form->text_input("httpcode","HTTP code [Apps->Server]",36,$slconfig->get_http_inbound_secret(),"Enter here");
if($session->get_ownerlevel() == 1)
{
    $form->col(6);
        $form->group("SMTP [Email sending support]");
        $form->text_input("smtp_from","From",30,$slconfig->get_smtp_from(),"From email address");
        $form->text_input("smtp_reply","Reply",30,$slconfig->get_smtp_replyto(),"Reply to email address");
        $form->text_input("smtp_host","Host",30,$slconfig->get_smtp_host(),"SMTP host");
        $form->text_input("smtp_user","Username",30,"skip","SMTP username (leave as skip to not update)");
        $form->text_input("smtp_code","Access code",30,"skip","SMTP access code [or password] (leave as skip to not update)");
        $form->text_input("smtp_port","Port",30,$slconfig->get_smtp_port(),"port to connect to for SMTP");
}
$form->col(6);
    $form->group("Resellers");
    $form->direct_add("<br/>");
    $form->select("new_resellers","Auto accept resellers",$slconfig->get_new_resellers(),array(false=>"No",true=>"Yes"));
    $form->text_input("new_resellers_rate","Auto accepted resellers rate",36,$slconfig->get_new_resellers_rate(),"1 to 100");
$form->col(6);
    $form->direct_add("<br/>");
    $form->group("Feature packs");
    $form->select("event_storage","Event storage",$slconfig->get_eventstorage(),array(false=>"Disabled",true=>"Enabled"));
$form->col(6);
    $form->direct_add("<br/>");
    $form->group("UI settings");
    $form->select("ui_tweaks_clients_fulllist","Clients [Full list]",$slconfig->get_clients_list_mode(),array(false=>"Disabled",true=>"Enabled"));
echo $form->render("Update","primary");
echo "<hr/>
Feature packs<br/>
<ul>
<li>Event storage: Stores events into the database in an unlinked format, once im happy with the code the centova API engine uses this to automate ^+^</li>
</ul>
</p>";
?>