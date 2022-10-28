<?php

namespace App\Endpoint\View\Tree;

use App\Endpoint\Secondlifeapi\Tree\GetPackages;
use App\Models\Sets\PackageSet;
use YAPF\Bootstrap\Template\Form;
use App\Models\Treevender;
use App\Models\Sets\ServertypesSet;
use App\Models\Sets\TreevenderpackagesSet;

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
        if ($treevender->loadID(intval($this->siteConfig->getPage())) == false) {
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

        $testOutput = new GetPackages();
        $testOutput->ProcessWithTreevenderID($treevender->getId());
        $testing = $testOutput->captureOutput();
        if (nullSafeStrLen($testing) > 9000) {
            $this->output->addSwapTagString(
                "page_content",
                '<div class="alert alert-danger" role="alert">The current setup will fail to talk with SL<br/>
                Please use less packages!</div>'
            );
        }

        $this->output->addSwapTagString("page_title", ":" . $treevender->getName());
        $form = new Form();
        $form->target("tree/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput("name", "Name", 30, $treevender->getName(), "Name");
        $form->select(
            "hideSoldout",
            "Hide soldout packages in SL",
            $treevender->getHideSoldout(),
            $this->disableEnable
        );
        $form->col(6);
        $form->group("Textures");
        $form->textureInput(
            "textureWaiting",
            "Waiting",
            36,
            $treevender->getTextureWaiting(),
            "UUID when waiting for a user"
        );
        $form->textureInput(
            "textureInuse",
            "In use",
            36,
            $treevender->getTextureInuse(),
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
            $used_package_ids[] = $treevender_package->getPackageLink();
            $entry[] = $treevender_package->getId();
            $entry[] = $improved_packageLinker[$treevender_package->getPackageLink()];

            $entry[] = "<a href='[[SITE_URL]]tree/removepackage/" . $treevender_package->getId() . "'>"
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
