<?php

namespace App\Endpoint\Control\Slconfig;

use App\Models\Avatar;
use App\Models\Timezones;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $this->output->purgeCacheFile("current_timezone", false);

        $avatar = new Avatar();
        $timezone = new Timezones();
        $input = new InputFilter();

        $slLinkCode = $input->postFilter("slLinkCode");
        $httpcode = $input->postFilter("httpcode");
        $publicLinkCode = $input->postFilter("publicLinkCode");
        $newResellersRate = $input->postFilter("newResellersRate", "integer");
        $newResellers = $input->postFilter("newResellers", "bool");
        $event_storage = $input->postFilter("event_storage", "bool");
        $owneravuid = $input->postFilter("owneravuid");
        $ui_tweaks_clients_fulllist = $input->postFilter("ui_tweaks_clients_fulllist", "bool");
        $ui_tweaks_datatableItemsPerPage = $input->postFilter("ui_tweaks_datatableItemsPerPage", "integer");
        $apiDefaultEmail = $input->postFilter("apiDefaultEmail", "email");
        $displayTimezoneLink = $input->postFilter("displayTimezoneLink", "integer");

        if (strlen($slLinkCode) < 5) {
            $this->setSwapTag("message", "slLinkCode length must be 5 or longer");
            return;
        }
        if (strlen($slLinkCode) > 10) {
            $this->setSwapTag("message", "slLinkCode length must be 10 or less");
            return;
        }
        if (strlen($httpcode) < 5) {
            $this->setSwapTag("message", "httpcode length must be 5 or longer");
            return;
        }
        if (strlen($httpcode) > 30) {
            $this->setSwapTag("message", "httpcode length must be 30 or less");
            return;
        }
        if ($newResellersRate < 0) {
            $this->setSwapTag("message", "newResellersRate must be 1 or more");
            return;
        }
        if ($newResellersRate > 100) {
            $this->setSwapTag("message", "newResellersRate must be 100 or less");
            return;
        }
        if ($ui_tweaks_datatableItemsPerPage < 10) {
            $this->setSwapTag("message", "Datatable entrys per page length must be 10 or more");
            return;
        }
        if ($ui_tweaks_datatableItemsPerPage > 200) {
            $this->setSwapTag("message", "Datatable entrys per page must be 200 or less");
            return;
        }
        if (strlen($owneravuid) != 8) {
            $this->setSwapTag("message", "Owner AV uid length must be 8");
            return;
        }
        if ($avatar->loadByField("avatarUid", $owneravuid) == false) {
            $this->setSwapTag("message", "Unable to load avatar from uid");
            return;
        }
        if ($timezone->loadID($displayTimezoneLink) == false) {
            $this->setSwapTag("message", "Timezone selected not supported");
            return;
        }
        if (strlen($apiDefaultEmail) < 7) {
            $this->setSwapTag("message", "API default email address does not appear to be vaild");
            return;
        }
        if (strlen($publicLinkCode) < 6) {
            $this->setSwapTag("message", "Public link code min length is 6");
            return;
        }
        if (strlen($publicLinkCode) > 12) {
            $this->setSwapTag("message", "Public link code max length is 12");
            return;
        }

        $this->setSwapTag("redirect", "slconfig");
        if ($avatar->getId() != $this->slconfig->getOwnerAvatarLink()) {
            $this->slconfig->setOwnerAvatarLink($avatar->getId());
        }
        $this->slconfig->setSlLinkCode($slLinkCode);
        $this->slconfig->setPublicLinkCode($publicLinkCode);
        $this->slconfig->setHttpInboundSecret($httpcode);
        $this->slconfig->setNewResellers($newResellers);
        $this->slconfig->setNewResellersRate($newResellersRate);
        $this->slconfig->setEventStorage($event_storage);
        $this->slconfig->setClientsListMode($ui_tweaks_clients_fulllist);
        $this->slconfig->setDatatableItemsPerPage($ui_tweaks_datatableItemsPerPage);
        $this->slconfig->setDisplayTimezoneLink($displayTimezoneLink);
        $this->slconfig->setApiDefaultEmail($apiDefaultEmail);
        if ($this->session->getOwnerLevel() == 1) {
            $smtpFrom = $input->postFilter("smtpFrom");
            $smtp_reply = $input->postFilter("smtp_reply");
            $smtpHost = $input->postFilter("smtpHost");
            $smtp_user = $input->postFilter("smtp_user");
            $smtp_code = $input->postFilter("smtp_code");
            $smtpPort = $input->postFilter("smtpPort");
            // missing tests here :P
            $this->slconfig->setSmtpHost($smtpHost);
            $this->slconfig->setSmtpPort($smtpPort);
            if ($smtp_user != "skip") {
                $this->slconfig->setSmtpUsername($smtp_user);
            }
            if ($smtp_code != "skip") {
                $this->slconfig->setSmtpAccesscode($smtp_code);
            }
            $this->slconfig->setSmtpFrom($smtpFrom);
            $this->slconfig->setSmtpReplyTo($smtp_reply);
        }
            $update_status = $this->slconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update system config: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "system config updated");
    }
}
