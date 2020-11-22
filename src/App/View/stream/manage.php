<?php

namespace App\View\Stream;

use App\ApisSet;
use App\PackageSet;
use App\ServerSet;
use App\ServertypesSet;
use App\Stream;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing stream");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]stream/remove/" . $this->page . "'>"
            . "<button type='button' class='btn btn-danger'>Remove</button></a>"
        );

        $stream = new Stream();
        if ($stream->loadByField("stream_uid", $this->page) == false) {
            $this->output->redirect("stream?bubblemessage=unable to find stream&bubbletype=warning");
            return;
        }
        $server_set = new ServerSet();
        $server_set->loadAll();

        $package_set = new PackageSet();
        $package_set->loadAll();

        $api_set = new ApisSet();
        $api_set->loadAll();

        $improved_serverlinker = [];
        foreach ($server_set->getAllIds() as $server_id) {
            $server = $server_set->getObjectByID($server_id);
            $api = $api_set->getObjectByID($server->getApilink());
            $improved_serverlinker[$server->getId()] = $server->getDomain() . " {" . $api->getName() . "}";
        }

        $servertypes_set = new ServertypesSet();
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
            $saddon2 = "";
            if ($package->getListeners() > 1) {
                $saddon2 = "'s";
            }
            $info = $package->getName();
            $info .= " @ ";
            $info .= $package->getDays();
            $info .= "day";
            $info .= $saddon;
            $info .= " - ";
            $info .= $autodjflag[$package->getAutodj()];
            $info .= " - ";
            $info .= $servertype->getName();
            $info .= " - ";
            $info .= $package->getBitrate();
            $info .= "kbs";
            $info .= " - ";
            $info .= $package->getListeners();
            $info .= "listener";
            $info .= $saddon2;
            $improved_packagelinker[$package->getId()] = $info;
        }

        $form = new Form();
        $form->target("stream/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->numberInput("port", "port", $stream->getPort(), 5, "Max 99999");
        $form->select("packagelink", "Package", $stream->getPackagelink(), $improved_packagelinker);
        $form->select("serverlink", "Server", $stream->getServerlink(), $improved_serverlinker);
        $form->textInput("mountpoint", "Mountpoint", 999, $stream->getMountpoint(), "Stream mount point");
        $form->col(6);
        $form->group("Config");
        $form->textInput(
            "original_adminusername",
            "Original admin Usr",
            5,
            $stream->getOriginal_adminusername(),
            "original adminusername [Restored by API if enabled]"
        );
        $form->textInput("adminusername", "Admin Usr", 5, $stream->getAdminusername(), "Admin username");
        $form->textInput("adminpassword", "Admin PW", 3, $stream->getAdminpassword(), "Admin password");
        $form->textInput(
            "djpassword",
            "Encoder/Stream password",
            3,
            $stream->getDjpassword(),
            "Encoder/Stream password"
        );
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("API");
        $form->textInput("api_uid_1", "API UID 1", 10, $stream->getApi_uid_1(), "API id 1");
        $form->textInput("api_uid_2", "API UID 2", 10, $stream->getApi_uid_2(), "API id 2");
        $form->textInput("api_uid_3", "API UID 3", 10, $stream->getApi_uid_3(), "API id 3");
        $form->col(6);
        $form->group("Magic");
        $form->select("api_update", "Update on server", 0, $this->yesNo);
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
        include "../App/View/Stream/api_linking.php";
    }
}
