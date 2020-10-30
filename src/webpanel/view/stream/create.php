<?php
$view_reply->add_swap_tag_string("html_title"," ~ Create");
$view_reply->add_swap_tag_string("page_title"," Create new stream");
$view_reply->set_swap_tag_string("page_actions","");

$server_set = new server_set();
$server_set->loadAll();

$package_set = new package_set();
$package_set->loadAll();

$api_set = new apis_set();
$api_set->loadAll();

$improved_serverlinker = [];
foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $api = $api_set->get_object_by_id($server->get_apilink());
    $improved_serverlinker[$server->get_id()] = $server->get_domain()." {".$api->get_name()."}";
}

$servertypes_set = new servertypes_set();
$servertypes_set->loadAll();

$autodjflag = array(true=>"{AutoDJ}",false=>"{StreamOnly}");
$improved_packagelinker = [];
foreach($package_set->get_all_ids() as $package_id)
{
    $package = $package_set->get_object_by_id($package_id);
    $servertype = $servertypes_set->get_object_by_id($package->get_servertypelink());
    $saddon = "";
    if($package->get_days() > 1)
    {
        $saddon = "'s";
    }
    $improved_packagelinker[$package->get_id()] = "".$package->get_name()." @ ".$package->get_days()."day".$saddon." - ".$autodjflag[$package->get_autodj()]." - ".$servertype->get_name()." - ".$package->get_bitrate()."kbs - ".$package->get_listeners()." listeners";
}


$form = new form();
$form->target("stream/create");
$form->required(true);
$form->col(6);
    $form->group("Basics");
    $form->number_input("port","port",null,5,"Max 99999");
    $form->select("packagelink","Package",0,$improved_packagelinker);
    $form->select("serverlink","Server",0,$improved_serverlinker);
    $form->text_input("mountpoint","Mountpoint",999,"/live","Stream mount point");
$form->col(6);
    $form->group("Config");
    $form->text_input("adminusername","Admin Usr",5,null,"Admin username");
    $form->text_input("adminpassword","Admin PW",3,null,"Admin password");
    $form->text_input("djpassword","Encoder/Stream password",3,null,"Encoder/Stream password");
    $form->select("needswork","Needs work",false,array(false=>"No",true=>"Yes"));
$form->direct_add("<br/>");
$form->col(6);
    $form->group("API");
    $form->text_input("api_uid_1","API UID 1",10,null,"API id 1");
    $form->text_input("api_uid_2","API UID 2",10,null,"API id 2");
    $form->text_input("api_uid_3","API UID 3",10,null,"API id 3");
$form->col(6);
    $form->group("Magic");
    $form->select("api_create","Create on server",0,array(0=>"No",1=>"Yes"));
$view_reply->set_swap_tag_string("page_content",$form->render("Create","primary"));
include "webpanel/view/stream/api_linking.php";
?>
