<?php

namespace App\Endpoint\View\Stream;

use App\R7\Set\ApisSet;
use App\R7\Set\PackageSet;
use App\R7\Set\ServerSet;
use App\R7\Set\ServertypesSet;
use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new stream");
        $this->setSwapTag("page_actions", "");

        $server_set = new ServerSet();
        $server_set->loadAll();

        $package_set = new PackageSet();
        $package_set->loadAll();

        $api_set = new ApisSet();
        $api_set->loadAll();

        $improved_serverLinker = [];
        foreach ($server_set->getAllIds() as $server_id) {
            $server = $server_set->getObjectByID($server_id);
            $api = $api_set->getObjectByID($server->getApiLink());
            $improved_serverLinker[$server->getId()] = $server->getDomain() . " {" . $api->getName() . "}";
        }

        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $autodjflag = [true => "{AutoDJ}",false => "{StreamOnly}"];
        $improved_packageLinker = [];
        foreach ($package_set->getAllIds() as $package_id) {
            $package = $package_set->getObjectByID($package_id);
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
        $form->target("stream/create");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->numberInput("port", "port", null, 5, "Max 99999");
        $form->select("packageLink", "Package", 0, $improved_packageLinker);
        $form->select("serverLink", "Server", 0, $improved_serverLinker);
        $form->textInput("mountpoint", "Mountpoint", 999, "/live", "Stream mount point");
        $form->col(6);
        $form->group("Config");
        $form->textInput("adminUsername", "Admin Usr", 5, null, "Admin username");
        $form->textInput("adminPassword", "Admin PW", 3, null, "Admin password");
        $form->textInput("djPassword", "Encoder/Stream password", 3, null, "Encoder/Stream password");
        $form->select("needswork", "Needs work", false, $this->yesNo);
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("API");
        $form->textInput("apiConfigValue1", "API UID 1", 10, null, "API id 1");
        $form->textInput("apiConfigValue2", "API UID 2", 10, null, "API id 2");
        $form->textInput("apiConfigValue3", "API UID 3", 10, null, "API id 3");
        $form->col(6);
        $form->group("Magic");
        $form->select("api_create", "Create on server", 0, $this->yesNo);
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
        include "" . ROOTFOLDER . "/App/Endpoint/View/Stream/api_linking.php";
    }
}
