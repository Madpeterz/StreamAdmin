<?php

namespace App\Endpoint\View\Stream;

use App\Models\Set\ApisSet;
use App\Models\Set\PackageSet;
use App\Models\Set\ServerSet;
use App\Models\Set\ServertypesSet;
use YAPF\Bootstrap\Template\Form;

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

        $improved_serverLinker = $server_set->getLinkedArray("id", "domain");

        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $autodjflag = [true => "{AutoDJ}",false => "{StreamOnly}"];
        $improved_packageLinker = [];
        foreach ($package_set as $package) {
            $servertype = $servertypes_set->getObjectByID($package->_ServertypeLink);
            $saddon = "";
            if ($package->_Days > 1) {
                $saddon = "'s";
            }
            $saddon2 = "";
            if ($package->_Listeners > 1) {
                $saddon2 = "'s";
            }
            $info = $package->_Name;
            $info .= " @ ";
            $info .= $package->_Days;
            $info .= "day";
            $info .= $saddon;
            $info .= " - ";
            $info .= $autodjflag[$package->_Autodj];
            $info .= " - ";
            $info .= $servertype->_Name;
            $info .= " - ";
            $info .= $package->_Bitrate;
            $info .= "kbs";
            $info .= " - ";
            $info .= $package->_Listeners;
            $info .= "listener";
            $info .= $saddon2;
            $improved_packageLinker[$package->_Id] = $info;
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
        $form->textInput("adminUsername", "Admin Usr", 50, null, "Admin username");
        $form->textInput("adminPassword", "Admin PW", 20, null, "Admin password");
        $form->textInput("djPassword", "Encoder/Stream password", 20, null, "Encoder/Stream password");
        $form->select("needswork", "Needs work", false, $this->yesNo);
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
