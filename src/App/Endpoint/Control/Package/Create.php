<?php

namespace App\Endpoint\Control\Package;

use App\Models\Package;
use App\Models\Servertypes;
use App\Models\Template;
use App\Models\Sets\NoticenotecardSet;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $template = new Template();
        $servertype = new Servertypes();

        $package = new Package();

        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();
        $noticeNotecardIds = $noticeNotecards->getAllIds();

        $name = $this->input->post("name")->checkStringLength(5, 30)->asString();
        $templateLink = $this->input->post("templateLink")->checkGrtThanEq(1)->asInt();
        $cost = $this->input->post("cost")->checkInRange(1, 99999)->asInt();
        $days = $this->input->post("days")->checkInRange(1, 999)->asInt();
        $bitrate = $this->input->post("bitrate")->checkInRange(56, 999)->asInt();
        $listeners = $this->input->post("listeners")->checkInRange(1, 999)->asInt();
        $textureSoldout = $this->input->post("textureSoldout")->isUuid()->asString();
        $textureInstockSmall = $this->input->post("textureInstockSmall")->isUuid()->asString();
        $textureInstockSelected = $this->input->post("textureInstockSelected")->isUuid()->asString();
        $enableGroupInvite = $this->input->post("enableGroupInvite")->asBool();
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
            if ($value === null) {
                $this->failed("Entry: " . $key . " is not set");
                return;
            }
        }


        $autodj = $this->input->post("autodj")->asBool();
        $autodjSize = $this->input->post("autodjSize")->checkInRange(1, 9999)->asInt();
        $servertypeLink = $this->input->post("servertypeLink")->checkGrtThanEq(1)->asInt();
        $welcomeNotecardLink = $this->input->post("welcomeNotecardLink")->checkGrtThanEq(1)->asInt();
        $setupNotecardLink = $this->input->post("setupNotecardLink")->checkGrtThanEq(1)->asInt();


        if (in_array($welcomeNotecardLink, $noticeNotecardIds) == false) {
            $this->failed("Welcome notecard not selected");
            return;
        }
        if (in_array($setupNotecardLink, $noticeNotecardIds) == false) {
            $this->failed("Setup notecard not selected");
            return;
        }
        if ($template->loadID($templateLink)->status == false) {
            $this->failed("Unable to find template");
            return;
        }
        if ($servertype->loadID($servertypeLink)->status == false) {
            $this->failed("Unable to find server type");
            return;
        }

        $this->setSwapTag("redirect", "package");
        $uid = $package->createUID("packageUid", 8);
        if ($uid->status == false) {
            $this->failed("Unable to assign a new UID to the package");
            return;
        }
        $package->setPackageUid($uid->uid);
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
        $package->setServertypeLink($servertypeLink);
        $package->setWelcomeNotecardLink($welcomeNotecardLink);
        $package->setSetupNotecardLink($setupNotecardLink);
        $package->setEnableGroupInvite($enableGroupInvite);
        $create_status = $package->createEntry();
        if ($create_status->status == false) {
            $this->failed(
                sprintf("Unable to create package: %1\$s", $create_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Package created");
        $this->createAuditLog($package->getId(), "+++", null, $package->getName());
    }
}
