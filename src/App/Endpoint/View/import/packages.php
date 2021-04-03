<?php

namespace App\Endpoint\View\Import;

use App\R4\Set\PackagesSet;
use App\R7\Model\Package;
use App\R7\Set\TemplateSet;

class Packages extends View
{
    public function process(): void
    {
        $r4_packages_set = new PackagesSet();
        $r4_packages_set->reconnectSql($this->oldSqlDB);
        $r4_packages_set->loadAll();

        global $sql;
        $sql = $this->realSqlDB;

        $template_set = new TemplateSet();
        $template_set->loadAll();

        $template = $template_set->getFirst();

        $all_ok = true;
        $packages_created = 0;
        foreach ($r4_packages_set->getAllIds() as $r4_package_id) {
            $r4_package = $r4_packages_set->getObjectByID($r4_package_id);
            $package = new Package();
            $uid = $package->createUID("packageUid", 8, 10);
            if ($uid["status"] == false) {
                $this->output->addSwapTagString("page_content", "Unable to create a package Uid");
                $all_ok = false;
                break;
            }
            $package->setPackageUid($uid["uid"]);
            $package->setName("R4|" . $r4_package->getId() . "|" . $r4_package->getName());
            $package->setAutodj($r4_package->getAutoDJ());
            $package->setAutodjSize(0);
            $package->setListeners($r4_package->getUsers());
            $package->setBitrate($r4_package->getStreamrate());
            $package->setTemplateLink($template->getId());
            $package->setCost($r4_package->getLcost());
            $package->setDays($r4_package->getSublength());
            if ($r4_package->getSoldouttexture() == null) {
                $package->setTextureSoldout("00000000-0000-0000-0000-000000000000");
            } else {
                $package->setTextureSoldout($r4_package->getSoldouttexture());
            }

            if ($r4_package->getInfotexture() == null) {
                $package->setTextureInstockSmall("00000000-0000-0000-0000-000000000000");
            } else {
                $package->setTextureInstockSmall($r4_package->getInfotexture());
            }

            if ($r4_package->getMaintexture() == null) {
                $package->setTextureInstockSelected("00000000-0000-0000-0000-000000000000");
            } else {
                $package->setTextureInstockSelected($r4_package->getMaintexture());
            }

            $create_status = $package->createEntry();
            if ($create_status["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create entry because: " . $create_status["message"]
                );
                $all_ok = false;
                break;
            }
            $packages_created++;
        }
        if ($all_ok == false) {
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $packages_created . " packages <br/> <a href=\"[[url_base]]import\">Back to menu</a>"
        );
    }
}
