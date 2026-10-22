<?php

namespace App\Endpoint\View\Tree;

use App\Endpoint\Secondlifeapi\Tree\Getpackages;
use App\Models\Set\PackageSet;
use YAPF\Bootstrap\Template\Form;
use App\Models\Treevender;
use App\Models\Set\ServertypesSet;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove tree vender " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove tree vender' 
        data-actionmessage='Are you sure you wish to remove this tree vender?' 
        data-targetendpoint='[[SITE_URL]]Tree/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $treevender = new Treevender();
        if ($treevender->loadID(intval($this->siteConfig->getPage()))->status == false) {
            $this->output->redirect("tree?bubblemessage=unable to find tree vender&bubbletype=warning");
            return;
        }
        $package_set = new PackageSet();
        $package_set->loadAll();
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

        $testOutput = new Getpackages();
        $testOutput->ProcessWithTreevenderID($treevender->_Id);
        $testing = $testOutput->captureOutput();
        if (nullSafeStrLen($testing) > 9000) {
            $this->output->addSwapTagString(
                "page_content",
                '<div class="alert alert-danger" role="alert">The current setup will fail to talk with SL<br/>
                Please use less packages!</div>'
            );
        }

        $this->output->addSwapTagString("page_title", ":" . $treevender->_Name);
        $form = new Form();
        $form->target("tree/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput("name", "Name", 30, $treevender->_Name, "Name");
        $form->select(
            "hideSoldout",
            "Hide soldout packages in SL",
            $treevender->_HideSoldout,
            $this->disableEnable
        );
        $form->col(6);
        $form->group("Textures");
        $form->textureInput(
            "textureWaiting",
            "Waiting",
            36,
            $treevender->_TextureWaiting,
            "UUID when waiting for a user"
        );
        $form->textureInput(
            "textureInuse",
            "In use",
            36,
            $treevender->_TextureInuse,
            "UUID when activly being used"
        );

        $this->setSwapTag("page_content", $form->render("Update", "primary"));
        $this->output->addSwapTagString("page_content", "<br/><hr/><br/>");
        $treevender_packages_set = $treevender->relatedTreevenderpackages();
        $table_head = ["ID","Name","Action"];
        $table_body = [];
        $used_package_ids = [];

        foreach ($treevender_packages_set as $treevender_package) {
            $entry = [];
            $used_package_ids[] = $treevender_package->_PackageLink;
            $entry[] = $treevender_package->_Id;
            $entry[] = $improved_packageLinker[$treevender_package->_PackageLink];

            $entry[] = "<a href='[[SITE_URL]]tree/removepackage/" . $treevender_package->_Id . "'>"
            . "<button type='button' class='btn btn-outline-danger btn-sm'>Remove</button></a>";
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body));
        $unUsed_index = [];
        foreach ($package_set->getAllIds() as $id) {
            if (in_array($id, $used_package_ids) == false) {
                $unUsed_index[$id] = $improved_packageLinker[$id];
            }
        }
        if (count($unUsed_index) > 0) {
            $form = new Form();
            $form->target("tree/addpackage/" . $this->siteConfig->getPage() . "");
            $form->select("package", "Package", "", $unUsed_index);
            $this->output->addSwapTagString("page_content", $form->render("Add package", "success"));
        }
    }
}
