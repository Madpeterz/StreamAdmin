<?php

namespace App\Endpoint\Control\Slconfig;

use App\Models\Avatar;
use App\Models\Timezones;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    protected function forceReissue(): bool
    {
        $reissued = false;
        if ($this->siteConfig->getSlConfig()->getSlLinkCode() == null) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->getSlLinkCode()) > 10) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->getPublicLinkCode()) > 12) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->getHudLinkCode()) > 12) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->getHttpInboundSecret()) > 12) {
            $reissued = true;
        }
        if ($reissued == true) {
            $reissueKeys = new ReIssue();
            $reissueKeys->reissueKeys();
        }
        return $reissued;
    }

    protected function updateHudSettings(): void
    {

        $hudAllowDiscord = $this->input->post("hudAllowDiscord")->asBool();
        $hudDiscordLink = $this->input->post("hudDiscordLink")->asString();
        if ($hudAllowDiscord == false) {
            $hudDiscordLink = null;
        }
        $hudAllowGroup = $this->input->post("hudAllowGroup")->asBool();
        $hudGroupLink = $this->input->post("hudGroupLink")->asString();
        if ($hudAllowGroup == false) {
            $hudGroupLink = null;
        }
        if (nullSafeStrLen($hudGroupLink) == 0) {
            $hudAllowGroup = false;
        }
        if (nullSafeStrLen($hudDiscordLink) == 0) {
            $hudAllowDiscord = false;
        }
        $hudAllowDetails = $this->input->post("hudAllowDetails")->asBool();
        $hudAllowRenewal = $this->input->post("hudAllowRenewal")->asBool();
        if ($hudAllowRenewal == false) {
            $hudAllowRenewal = $hudAllowDetails; // Unable to have renewal without details
        }
        $this->siteConfig->getSlConfig()->setHudAllowDiscord($hudAllowDiscord);
        $this->siteConfig->getSlConfig()->setHudDiscordLink($hudDiscordLink);
        $this->siteConfig->getSlConfig()->setHudAllowGroup($hudAllowGroup);
        $this->siteConfig->getSlConfig()->setHudGroupLink($hudGroupLink);
        $this->siteConfig->getSlConfig()->setHudAllowDetails($hudAllowDetails);
        $this->siteConfig->getSlConfig()->setHudAllowRenewal($hudAllowRenewal);
    }

    public function process(): void
    {
        $avatar = new Avatar();
        $timezone = new Timezones();


        $newResellersRate = $this->input->post("newResellersRate")->checkInRange(1, 100)->asInt();
        if ($newResellersRate === null) {
            $this->failed($this->input->getWhyFailed());
            return;
        }
        $newResellers = $this->input->post("newResellers")->asBool();
        $owneravuid = $this->input->post("owneravuid")->checkStringLength(6, 8)->asString();
        if ($owneravuid === null) {
            $this->failed("owneravUID is invaild:" . $this->input->getWhyFailed());
            return;
        }
        $ui_tweaks_clientsShowServer = $this->input->post("ui_tweaks_clientsShowServer")->asBool();
        if ($ui_tweaks_clientsShowServer === null) {
            $this->failed("Invaild server on clients option:" . $this->input->getWhyFailed());
            return;
        }
        $ui_tweaks_groupStreamsBy = $this->input->post("ui_tweaks_groupStreamsBy")->checkInRange(0, 1)->asInt();
        if ($ui_tweaks_groupStreamsBy === null) {
            $this->failed("Invaild group streams by option:" . $this->input->getWhyFailed());
            return;
        }
        $ui_tweaks_clients_fulllist = $this->input->post("ui_tweaks_clients_fulllist")->asBool();
        $ui_tweaks_datatableItemsPerPage = $this->input
            ->post("ui_tweaks_datatableItemsPerPage")
            ->checkInRange(10, 200)
            ->asInt();
        if ($ui_tweaks_datatableItemsPerPage === null) {
            $this->failed("Invaild datatable items per page:" . $this->input->getWhyFailed());
            return;
        }
        $displayTimezoneLink = $this->input->post("displayTimezoneLink")->checkGrtThanEq(1)->asInt();
        if ($displayTimezoneLink === null) {
            $this->failed("Invaild timezone selected:" . $this->input->getWhyFailed());
            return;
        }
        $eventsAPI = $this->input->post("eventsAPI")->asBool();
        if ($avatar->loadByAvatarUid($owneravuid) == false) {
            $this->failed("Unable to load avatar from uid");
            return;
        }
        if ($timezone->loadID($displayTimezoneLink)->status == false) {
            $this->failed("Timezone selected not supported");
            return;
        }

        $this->setSwapTag("redirect", "slconfig");
        if ($avatar->getId() != $this->siteConfig->getSlConfig()->getOwnerAvatarLink()) {
            $this->siteConfig->getSlConfig()->setOwnerAvatarLink($avatar->getId());
        }
        $oldvalues = $this->siteConfig->getSlConfig()->objectToValueArray();
        $this->siteConfig->getSlConfig()->setNewResellers($newResellers);
        $this->siteConfig->getSlConfig()->setNewResellersRate($newResellersRate);
        $this->siteConfig->getSlConfig()->setClientsListMode($ui_tweaks_clients_fulllist);
        $this->siteConfig->getSlConfig()->setDatatableItemsPerPage($ui_tweaks_datatableItemsPerPage);
        $this->siteConfig->getSlConfig()->setDisplayTimezoneLink($displayTimezoneLink);
        $this->siteConfig->getSlConfig()->setEventsAPI($eventsAPI);
        $this->siteConfig->getSlConfig()->setClientsDisplayServer($ui_tweaks_clientsShowServer);
        $this->siteConfig->getSlConfig()->setStreamListOption($ui_tweaks_groupStreamsBy);
        $this->updateHudSettings();
        $newvalues = $this->siteConfig->getSlConfig()->objectToValueArray();
        $reissuedKeys = $this->forceReissue();
        $update_status = $this->siteConfig->getSlConfig()->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update system config: %1\$s", $update_status->message)
            );
            return;
        }

        $this->redirectWithMessage("System config updated");
        if ($reissuedKeys == true) {
            $this->output->addSwapTagString("message", " [Forced key reissue]");
        }
        $this->createMultiAudit(
            $this->siteConfig->getSlConfig()->getId(),
            $this->siteConfig->getSlConfig()->getFields(),
            $oldvalues,
            $newvalues
        );
    }
}
