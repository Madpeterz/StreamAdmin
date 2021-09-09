<?php

namespace App\Endpoint\View\Stream;

use App\R7\Set\ApisSet;
use App\R7\Set\PackageSet;
use App\R7\Set\ServerSet;
use App\R7\Set\ServertypesSet;
use App\R7\Model\Stream;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing stream");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]stream/remove/" . $this->page . "'>"
            . "<button type='button' class='btn btn-danger'>Remove</button></a>"
        );

        $stream = new Stream();
        if ($stream->loadByField("streamUid", $this->page) == false) {
            $this->output->redirect("stream?bubblemessage=unable to find stream&bubbletype=warning");
            return;
        }
        $server_set = new ServerSet();
        $server_set->loadAll();

        $package_set = new PackageSet();
        $package_set->loadAll();

        $api_set = new ApisSet();
        $api_set->loadAll();

        $improved_serverLinker = [];
        foreach ($server_set as $server) {
            $api = $api_set->getObjectByID($server->getApiLink());
            $improved_serverLinker[$server->getId()] = $server->getDomain() . " {" . $api->getName() . "}";
        }

        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $autodjflag = [true => "{AutoDJ}",false => "{StreamOnly}"];
        $improved_packageLinker = [];
        foreach ($package_set as $package) {
            $servertype = $servertypes_set->getObjectByID($package->getServertypeLink());
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
            $improved_packageLinker[$package->getId()] = $info;
        }

        $form = new Form();
        $form->target("stream/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->numberInput("port", "port", $stream->getPort(), 5, "Max 99999");
        $form->select("packageLink", "Package", $stream->getPackageLink(), $improved_packageLinker);
        $form->select("serverLink", "Server", $stream->getServerLink(), $improved_serverLinker);
        $form->textInput("mountpoint", "Mountpoint", 999, $stream->getMountpoint(), "Stream mount point");
        $form->col(6);
        $form->group("Config");
        $form->textInput(
            "originalAdminUsername",
            "Original admin Usr",
            5,
            $stream->getOriginalAdminUsername(),
            "original adminUsername [Restored by API if enabled]"
        );
        $form->textInput("adminUsername", "Admin Usr", 5, $stream->getAdminUsername(), "Admin username");
        $form->textInput("adminPassword", "Admin PW", 3, $stream->getAdminPassword(), "Admin password");
        $form->textInput(
            "djPassword",
            "Encoder/Stream password",
            3,
            $stream->getDjPassword(),
            "Encoder/Stream password"
        );
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("API");
        $form->textInput("apiConfigValue1", "API UID 1", 10, $stream->getApiConfigValue1(), "API id 1");
        $form->textInput("apiConfigValue2", "API UID 2", 10, $stream->getApiConfigValue2(), "API id 2");
        $form->textInput("apiConfigValue3", "API UID 3", 10, $stream->getApiConfigValue3(), "API id 3");
        $form->col(6);
        $form->group("Magic");
        $form->select("api_update", "Update on server", 0, $this->yesNo);
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
        include "" . ROOTFOLDER . "/App/Endpoint/View/Stream/api_linking.php";
    }
}
