<?php

$this->output->addSwapTagString("html_title", " ~ Create");
$this->output->addSwapTagString("page_title", " Create new stream");
$this->output->setSwapTagString("page_actions", "");

$server_set = new server_set();
$server_set->loadAll();

$package_set = new package_set();
$package_set->loadAll();

$api_set = new apis_set();
$api_set->loadAll();

$improved_serverlinker = [];
foreach ($server_set->getAllIds() as $server_id) {
    $server = $server_set->getObjectByID($server_id);
    $api = $api_set->getObjectByID($server->get_apilink());
    $improved_serverlinker[$server->getId()] = $server->getDomain() . " {" . $api->getName() . "}";
}

$servertypes_set = new servertypes_set();
$servertypes_set->loadAll();

$autodjflag = [true => "{AutoDJ}",false => "{StreamOnly}"];
$improved_packagelinker = [];
foreach ($package_set->getAllIds() as $package_id) {
    $package = $package_set->getObjectByID($package_id);
    $servertype = $servertypes_set->getObjectByID($package->getServertypelink());
    $saddon = "";
    if ($package->getDays() > 1) {
        $saddon = "'s";
    }
    $improved_packagelinker[$package->getId()] = "" . $package->getName() . " @ " . $package->getDays() . "day" . $saddon . " - " . $autodjflag[$package->getAutodj()] . " - " . $servertype->getName() . " - " . $package->getBitrate() . "kbs - " . $package->getListeners() . " listeners";
}


$form = new form();
$form->target("stream/create");
$form->required(true);
$form->col(6);
    $form->group("Basics");
    $form->numberInput("port", "port", null, 5, "Max 99999");
    $form->select("packagelink", "Package", 0, $improved_packagelinker);
    $form->select("serverlink", "Server", 0, $improved_serverlinker);
    $form->textInput("mountpoint", "Mountpoint", 999, "/live", "Stream mount point");
$form->col(6);
    $form->group("Config");
    $form->textInput("adminusername", "Admin Usr", 5, null, "Admin username");
    $form->textInput("adminpassword", "Admin PW", 3, null, "Admin password");
    $form->textInput("djpassword", "Encoder/Stream password", 3, null, "Encoder/Stream password");
    $form->select("needswork", "Needs work", false, [false => "No",true => "Yes"]);
$form->directAdd("<br/>");
$form->col(6);
    $form->group("API");
    $form->textInput("api_uid_1", "API UID 1", 10, null, "API id 1");
    $form->textInput("api_uid_2", "API UID 2", 10, null, "API id 2");
    $form->textInput("api_uid_3", "API UID 3", 10, null, "API id 3");
$form->col(6);
    $form->group("Magic");
    $form->select("api_create", "Create on server", 0, [0 => "No",1 => "Yes"]);
$this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
include "webpanel/view/stream/api_linking.php";
