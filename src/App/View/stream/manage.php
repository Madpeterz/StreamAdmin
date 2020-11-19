<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing stream");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]stream/remove/" . $this->page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

$stream = new stream();
if ($stream->loadByField("stream_uid", $this->page) == true) {
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
        $improved_serverlinker[$server->getId()] = $server->get_domain() . " {" . $api->getName() . "}";
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
    $form->target("stream/update/" . $this->page . "");
    $form->required(true);
    $form->col(6);
        $form->group("Basics");
        $form->numberInput("port", "port", $stream->get_port(), 5, "Max 99999");
        $form->select("packagelink", "Package", $stream->get_packagelink(), $improved_packagelinker);
        $form->select("serverlink", "Server", $stream->get_serverlink(), $improved_serverlinker);
        $form->textInput("mountpoint", "Mountpoint", 999, $stream->get_mountpoint(), "Stream mount point");
    $form->col(6);
        $form->group("Config");
        $form->textInput("original_adminusername", "Original admin Usr", 5, $stream->get_original_adminusername(), "original adminusername [Restored by API if enabled]");
        $form->textInput("adminusername", "Admin Usr", 5, $stream->get_adminusername(), "Admin username");
        $form->textInput("adminpassword", "Admin PW", 3, $stream->get_adminpassword(), "Admin password");
        $form->textInput("djpassword", "Encoder/Stream password", 3, $stream->get_djpassword(), "Encoder/Stream password");
    $form->directAdd("<br/>");
    $form->col(6);
        $form->group("API");
        $form->textInput("api_uid_1", "API UID 1", 10, $stream->get_api_uid_1(), "API id 1");
        $form->textInput("api_uid_2", "API UID 2", 10, $stream->get_api_uid_2(), "API id 2");
        $form->textInput("api_uid_3", "API UID 3", 10, $stream->get_api_uid_3(), "API id 3");
    $form->col(6);
        $form->group("Magic");
        $form->select("api_update", "Update on server", 0, [0 => "No",1 => "Yes"]);
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    include "webpanel/view/stream/api_linking.php";
} else {
    $this->output->redirect("stream?bubblemessage=unable to find stream&bubbletype=warning");
}
