<?php

namespace App\Endpoint\Control\Package;

use App\R7\Model\Package;
use App\R7\Model\Servertypes;
use App\R7\Model\Template;
use App\R7\Set\NoticenotecardSet;
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

        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();
        $noticeNotecardIds = $noticeNotecards->getAllIds();

        $name = $input->postString("name", 30, 5);
        $templateLink = $input->postInteger("templateLink", false, true);
        $cost = $input->postInteger("cost", false, true);
        $days = $input->postInteger("days", false, true);
        $bitrate = $input->postInteger("bitrate", false, true);
        $listeners = $input->postInteger("listeners", false, true);
        $textureSoldout = $input->postUUID("textureSoldout");
        $textureInstockSmall = $input->postUUID("textureInstockSmall");
        $textureInstockSelected = $input->postUUID("textureInstockSelected");
        $enableGroupInvite = $input->postBool("enableGroupInvite");
        $testing = [
            "name" => $name,
            "template" => $templateLink,
            "cost" => $cost,
            "days" => $days,
            "bitrate" => $bitrate,
            "listeners" => $listeners,
            "texture soldout" => $textureSoldout,
            "texture small" => $textureInstockSmall,
            "texture selected" => $textureInstockSelected,
        ];
        $testing = array_reverse($testing, true);
        foreach ($testing as $key => $value) {
            if ($value == null) {
                $this->failed("Entry: " . $key . " is not set - " . $input->getWhyFailed());
                return;
            }
        }


        $autodj = $input->postBool("autodj");
        $autodjSize = $input->postInteger("autodjSize");
        $apiTemplate = $input->postString("apiTemplate");
        $servertypeLink = $input->postInteger("servertypeLink");
        $welcomeNotecardLink = $input->postInteger("welcomeNotecardLink");
        $setupNotecardLink = $input->postInteger("setupNotecardLink");


        if (in_array($welcomeNotecardLink, $noticeNotecardIds) == false) {
            $this->failed("Welcome notecard not selected");
            return;
        }
        if (in_array($setupNotecardLink, $noticeNotecardIds) == false) {
            $this->failed("Setup notecard not selected");
            return;
        }

        if ($cost > 99999) {
            $this->failed("Cost must be 99999 or less");
            return;
        }
        if ($days > 999) {
            $this->failed("Days must be 999 or less");
            return;
        }
        if ($bitrate < 56) {
            $this->failed("bitrate must be 56 or more");
            return;
        }
        if ($bitrate > 999) {
            $this->failed("bitrate must be 999 or less");
            return;
        }
        if ($listeners > 999) {
            $this->failed("listeners must be 999 or less");
            return;
        }
        if (strlen($textureSoldout) != 36) {
            $this->failed("Texture sold out must be a uuid");
            return;
        }
        if (strlen($textureInstockSmall) != 36) {
            $this->failed("Texture instock small out must be a uuid");
            return;
        }
        if (strlen($textureInstockSelected) != 36) {
            $this->failed("Texture instock selected out must be a uuid");
            return;
        }
        if ($autodjSize > 9999) {
            $this->failed("AutoDJ size must be 9999 or less");
            return;
        }
        if ($template->loadID($templateLink) == false) {
            $this->failed("Unable to find template");
            return;
        }
        if (strlen($apiTemplate) > 50) {
            $this->failed("API template name can not be longer than 50");
            return;
        }
        if (strlen($apiTemplate) < 3) {
            $this->failed("API template name can not be shorter than 3");
            return;
        }
        if ($servertype->loadID($servertypeLink) == false) {
            $this->failed("Unable to find server type");
            return;
        }

        $this->setSwapTag("redirect", "package");
        $uid = $package->createUID("packageUid", 8, 10);
        if ($uid["status"] == false) {
            $this->failed("Unable to assign a new UID to the package");
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
        $package->setWelcomeNotecardLink($welcomeNotecardLink);
        $package->setSetupNotecardLink($setupNotecardLink);
        $package->setEnableGroupInvite($enableGroupInvite);
        $create_status = $package->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
                sprintf("Unable to create package: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->ok("Package created");
    }
}
