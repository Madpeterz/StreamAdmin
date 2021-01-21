<?php

namespace App\Endpoint\Control\Package;

use App\Models\Package;
use App\Models\Servertypes;
use App\Models\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $template = new Template();
        $servertype = new Servertypes();
        $input = new InputFilter();
        $package = new Package();

        $name = $input->postFilter("name");
        $templateLink = $input->postFilter("templateLink", "integer");
        $cost = $input->postFilter("cost", "integer");
        $days = $input->postFilter("days", "integer");
        $bitrate = $input->postFilter("bitrate", "integer");
        $listeners = $input->postFilter("listeners", "integer");
        $textureSoldout = $input->postFilter("textureSoldout", "uuid");
        $textureInstockSmall = $input->postFilter("textureInstockSmall", "uuid");
        $textureInstockSelected = $input->postFilter("textureInstockSelected", "uuid");
        $autodj = $input->postFilter("autodj", "bool");
        $autodjSize = $input->postFilter("autodjSize", "integer");
        $apiTemplate = $input->postFilter("apiTemplate");
        $servertypeLink = $input->postFilter("servertypeLink", "integer");

        if (strlen($name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 60) {
            $this->setSwapTag("message", "Name must be 30 or less");
            return;
        }
        if ($cost < 1) {
            $this->setSwapTag("message", "Cost must be 1 or more");
            return;
        }
        if ($cost > 99999) {
            $this->setSwapTag("message", "Cost must be 99999 or less");
            return;
        }
        if ($days < 1) {
            $this->setSwapTag("message", "Days must be 1 or more");
            return;
        }
        if ($days > 999) {
            $this->setSwapTag("message", "Days must be 999 or less");
            return;
        }
        if ($bitrate < 56) {
            $this->setSwapTag("message", "bitrate must be 56 or more");
            return;
        }
        if ($bitrate > 999) {
            $this->setSwapTag("message", "bitrate must be 999 or less");
            return;
        }
        if ($listeners < 1) {
            $this->setSwapTag("message", "listeners must be 1 or more");
            return;
        }
        if ($listeners > 999) {
            $this->setSwapTag("message", "listeners must be 999 or less");
            return;
        }
        if (strlen($textureSoldout) != 36) {
            $this->setSwapTag("message", "Texture sold out must be a uuid");
            return;
        }
        if (strlen($textureInstockSmall) != 36) {
            $this->setSwapTag("message", "Texture instock small out must be a uuid");
            return;
        }
        if (strlen($textureInstockSelected) != 36) {
            $this->setSwapTag("message", "Texture instock selected out must be a uuid");
            return;
        }
        if ($autodjSize > 9999) {
            $this->setSwapTag("message", "AutoDJ size must be 9999 or less");
            return;
        }
        if ($template->loadID($templateLink) == false) {
            $this->setSwapTag("message", "Unable to find template");
            return;
        }
        if (strlen($apiTemplate) > 50) {
            $this->setSwapTag("message", "API template name can not be longer than 50");
            return;
        }
        if (strlen($apiTemplate) < 3) {
            $this->setSwapTag("message", "API template name can not be shorter than 3");
            return;
        }
        if ($servertype->loadID($servertypeLink) == false) {
            $this->setSwapTag("message", "Unable to find server type");
            return;
        }

        $this->setSwapTag("redirect", "package");
        $uid = $package->createUID("packageUid", 8, 10);
        if ($uid["status"] == false) {
            $this->setSwapTag("message", "Unable to assign a new UID to the package");
            return;
        }
        $package->setPackageUid($uid["uid"]);
        $package->setName($name);
        $package->setAutodj($autodj);
        $package->setAutodjSize($autodjSize);
        $package->setListeners($listeners);
        $package->setBitrate($bitrate);
        $package->setTemplateLink($templateLink);
        $package->setCost($cost);
        $package->setDays($days);
        $package->setTextureSoldout($textureSoldout);
        $package->setTextureInstockSmall($textureInstockSmall);
        $package->setTextureInstockSelected($textureInstockSelected);
        $package->setApiTemplate($apiTemplate);
        $package->setServertypeLink($servertypeLink);
        $create_status = $package->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create package: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Package created");
    }
}
