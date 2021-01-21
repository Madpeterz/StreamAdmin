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
        $templatelink = $input->postFilter("templatelink", "integer");
        $cost = $input->postFilter("cost", "integer");
        $days = $input->postFilter("days", "integer");
        $bitrate = $input->postFilter("bitrate", "integer");
        $listeners = $input->postFilter("listeners", "integer");
        $texture_uuid_soldout = $input->postFilter("texture_uuid_soldout", "uuid");
        $texture_uuid_instock_small = $input->postFilter("texture_uuid_instock_small", "uuid");
        $texture_uuid_instock_selected = $input->postFilter("texture_uuid_instock_selected", "uuid");
        $autodj = $input->postFilter("autodj", "bool");
        $autodj_size = $input->postFilter("autodj_size", "integer");
        $api_template = $input->postFilter("api_template");
        $servertypelink = $input->postFilter("servertypelink", "integer");

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
        if (strlen($texture_uuid_soldout) != 36) {
            $this->setSwapTag("message", "Texture sold out must be a uuid");
            return;
        }
        if (strlen($texture_uuid_instock_small) != 36) {
            $this->setSwapTag("message", "Texture instock small out must be a uuid");
            return;
        }
        if (strlen($texture_uuid_instock_selected) != 36) {
            $this->setSwapTag("message", "Texture instock selected out must be a uuid");
            return;
        }
        if ($autodj_size > 9999) {
            $this->setSwapTag("message", "AutoDJ size must be 9999 or less");
            return;
        }
        if ($template->loadID($templatelink) == false) {
            $this->setSwapTag("message", "Unable to find template");
            return;
        }
        if (strlen($api_template) > 50) {
            $this->setSwapTag("message", "API template name can not be longer than 50");
            return;
        }
        if (strlen($api_template) < 3) {
            $this->setSwapTag("message", "API template name can not be shorter than 3");
            return;
        }
        if ($servertype->loadID($servertypelink) == false) {
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
        $package->setAutodj_size($autodj_size);
        $package->setListeners($listeners);
        $package->setBitrate($bitrate);
        $package->setTemplatelink($templatelink);
        $package->setCost($cost);
        $package->setDays($days);
        $package->setTexture_uuid_soldout($texture_uuid_soldout);
        $package->setTexture_uuid_instock_small($texture_uuid_instock_small);
        $package->setTexture_uuid_instock_selected($texture_uuid_instock_selected);
        $package->setApi_template($api_template);
        $package->setServertypelink($servertypelink);
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
