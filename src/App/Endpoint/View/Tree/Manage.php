<?php

namespace App\Endpoint\View\Tree;

use App\R7\Set\PackageSet;
use App\Template\Form;
use App\R7\Model\Treevender;
use App\R7\Set\ServertypesSet;
use App\R7\Set\TreevenderpackagesSet;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", "<a href='[[url_base]]tree/remove/" . $this->page
        . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
        $treevender = new Treevender();
        if ($treevender->loadID(intval($this->page)) == false) {
            $this->output->redirect("tree?bubblemessage=unable to find tree vender&bubbletype=warning");
            return;
        }
        $package_set = new PackageSet();
        $package_set->loadAll();
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


        $this->output->addSwapTagString("page_title", ":" . $treevender->getName());
        $form = new Form();
        $form->target("tree/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput("name", "Name", 30, $treevender->getName(), "Name");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
        $this->output->addSwapTagString("page_content", "<br/><hr/><br/>");
        $treevender_packages_set = new TreevenderpackagesSet();
        $treevender_packages_set->loadOnField("treevenderLink", $treevender->getId());
        $table_head = ["ID","Name","Action"];
        $table_body = [];
        $used_package_ids = [];

        foreach ($treevender_packages_set->getAllIds() as $treevender_packages_id) {
            $treevender_package = $treevender_packages_set->getObjectByID($treevender_packages_id);
            $entry = [];
            $used_package_ids[] = $treevender_package->getPackageLink();
            $entry[] = $treevender_package->getId();
            $entry[] = $improved_packageLinker[$treevender_package->getPackageLink()];

            $entry[] = "<a href='[[url_base]]tree/removepackage/" . $treevender_package->getId() . "'>"
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
            $form->target("tree/addpackage/" . $this->page . "");
            $form->select("package", "Package", "", $unUsed_index);
            $this->output->addSwapTagString("page_content", $form->render("Add package", "success"));
        }
    }
}
