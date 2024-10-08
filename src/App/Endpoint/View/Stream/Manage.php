<?php

namespace App\Endpoint\View\Stream;

use App\Models\Rental;
use App\Models\Sets\ApisSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\ServertypesSet;
use App\Models\Stream;
use YAPF\Bootstrap\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing stream");

        $remove_action = ""
        . "<button type='button' 
        data-actiontitle='Remove stream " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove stream' 
        data-actionmessage='This will fail if there are pending actions!' 
        data-targetendpoint='[[SITE_URL]]Stream/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>";


        $stream = new Stream();
        if ($stream->loadByField("streamUid", $this->siteConfig->getPage())->status == false) {
            $this->output->redirect("stream?bubblemessage=unable to find stream&bubbletype=warning");
            return;
        }

        if ($stream->getNeedWork() == true) {
            $remove_action .= ""
            . " &nbsp;&nbsp;&nbsp;<button type='button' 
            data-actiontitle='Restore stream " . $this->siteConfig->getPage() . "' 
            data-actiontext='Restore stream' 
            data-actionmessage='Attempt to find the last owner of the stream and restore it to them' 
            data-targetendpoint='[[SITE_URL]]Stream/Restore/" . $this->siteConfig->getPage() . "' 
            class='btn btn-info confirmDialog'>Restore</button></a>";
        }

        $this->setSwapTag("page_actions", $remove_action);

        $rental = new Rental();
        $rental->loadByStreamLink($stream->getId());
        if ($rental->getId() > 0) {
            $this->setSwapTag(
                "page_actions",
                "<a href='[[SITE_URL]]Client/Manage/" . $rental->getRentalUid() . "'>"
                . "<button type='button' class='btn btn-info'>View Client</button></a>"
            );
        }

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
        $form->target("stream/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->numberInput("port", "port", $stream->getPort(), 5, "Max 99999");
        $form->select("packageLink", "Package", $stream->getPackageLink(), $improved_packageLinker);
        $form->select("serverLink", "Server", $stream->getServerLink(), $improved_serverLinker);
        $form->textInput("mountpoint", "Mountpoint", 999, $stream->getMountpoint(), "Stream mount point");
        $form->col(6);
        $form->group("Config");
        $form->textInput("adminUsername", "Admin Usr", 50, $stream->getAdminUsername(), "Admin username");
        $form->textInput("adminPassword", "Admin PW", 20, $stream->getAdminPassword(), "Admin password");
        $form->textInput(
            "djPassword",
            "Encoder/Stream password",
            20,
            $stream->getDjPassword(),
            "Encoder/Stream password"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
