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
        if ($this->siteConfig->getSlConfig()->_SlLinkCode == null) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->_SlLinkCode) > 10) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->_PublicLinkCode) > 12) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->_HudLinkCode) > 12) {
            $reissued = true;
        } elseif (nullSafeStrLen($this->siteConfig->getSlConfig()->_HttpInboundSecret) > 12) {
            $reissued = true;
        }
        if ($reissued == true) {
            $reissueKeys = new ReIssue();
            $reissueKeys->reissueKeys();
        }
        return $reissued;
    }

    protected function updateSystemLimits(): void
    {
        $limitStreams = $this->input->post("limitStreams")->asBool(false);
        $limitTime = $this->input->post("limitTime")->asBool(false);
        $maxStreamTimeDays = $this->input->post("maxStreamTimeDays")->checkInRange(7, 365)->asInt(7);
        $maxTotalStreams = $this->input->post("maxTotalStreams")->checkInRange(1, 2000)->asInt(50);
        $this->siteConfig->getSlConfig()->_LimitStreams = $limitStreams;
        $this->siteConfig->getSlConfig()->_LimitTime = $limitTime;
        $this->siteConfig->getSlConfig()->_MaxStreamTimeDays = $maxStreamTimeDays;
        $this->siteConfig->getSlConfig()->_MaxTotalStreams = $maxTotalStreams;
    }

    protected function updateHudSettings(): void
    {

        $hudAllowDiscord = $this->input->post("hudAllowDiscord")->asBool();
        $hudDiscordLink = $this->input->post("hudDiscordLink")->asString("");
        if ($hudAllowDiscord == false) {
            $hudDiscordLink = "";
        }
        $hudAllowGroup = $this->input->post("hudAllowGroup")->asBool();
        $hudGroupLink = $this->input->post("hudGroupLink")->asString("");
        if ($hudAllowGroup == false) {
            $hudGroupLink = "";
        }
        if (nullSafeStrLen($hudGroupLink) == 0) {
            $hudAllowGroup = false;
        }
        if (nullSafeStrLen($hudDiscordLink) == 0) {
            $hudAllowDiscord = false;
        }
        $hudAllowDetails = $this->input->post("hudAllowDetails")->asBool();
        $hudAllowRenewal = $this->input->post("hudAllowRenewal")->asBool();
        if ($hudAllowDetails == false) {
            $hudAllowRenewal = false; // Unable to have renewal without details
        }
        if ($this->siteConfig->getSlConfig()->_LimitTime == true) {
            $hudAllowRenewal = false; // Hud renewals bypass the rental time limit
        }
        $this->siteConfig->getSlConfig()->_HudAllowDiscord = $hudAllowDiscord;
        $this->siteConfig->getSlConfig()->_HudDiscordLink = $hudDiscordLink;
        $this->siteConfig->getSlConfig()->_HudAllowGroup = $hudAllowGroup;
        $this->siteConfig->getSlConfig()->_HudGroupLink = $hudGroupLink;
        $this->siteConfig->getSlConfig()->_HudAllowDetails = $hudAllowDetails;
        $this->siteConfig->getSlConfig()->_HudAllowRenewal = $hudAllowRenewal;
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
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $enableCoupons = $this->input->post("enableCoupons")->asBool();
            $ansSalt = $this->input->post("ansSalt")->checkStringLengthMax(50)->asString();
            $this->siteConfig->getSlConfig()->_AnsSalt = $ansSalt;
            $this->siteConfig->getSlConfig()->_EnableCoupons = $enableCoupons;
        }
        $this->setSwapTag("redirect", "slconfig");
        if ($avatar->_Id != $this->siteConfig->getSlConfig()->_OwnerAvatarLink) {
            $this->siteConfig->getSlConfig()->_OwnerAvatarLink = $avatar->_Id;
        }
        $oldvalues = $this->siteConfig->getSlConfig()->objectToValueArray();
        $this->siteConfig->getSlConfig()->_NewResellers = $newResellers;
        $this->siteConfig->getSlConfig()->_NewResellersRate = $newResellersRate;
        $this->siteConfig->getSlConfig()->_ClientsListMode = $ui_tweaks_clients_fulllist;
        $this->siteConfig->getSlConfig()->_DatatableItemsPerPage = $ui_tweaks_datatableItemsPerPage;
        $this->siteConfig->getSlConfig()->_DisplayTimezoneLink = $displayTimezoneLink;
        $this->siteConfig->getSlConfig()->_EventsAPI = $eventsAPI;
        $this->siteConfig->getSlConfig()->_ClientsDisplayServer = $ui_tweaks_clientsShowServer;
        $this->siteConfig->getSlConfig()->_StreamListOption = $ui_tweaks_groupStreamsBy;
        $this->updateSystemLimits();
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
            $this->siteConfig->getSlConfig()->_Id,
            $this->siteConfig->getSlConfig()->getFields(),
            $oldvalues,
            $newvalues
        );
    }
}
