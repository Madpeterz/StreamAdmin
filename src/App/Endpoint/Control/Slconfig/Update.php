<?php

namespace App\Endpoint\Control\Slconfig;

use App\R7\Model\Avatar;
use App\R7\Model\Timezones;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    protected function forceReissue(): bool
    {
        $reissued = false;
        if (strlen($this->slconfig->getSlLinkCode()) > 10) {
            $reissued = true;
        }
        if (strlen($this->slconfig->getPublicLinkCode()) > 12) {
            $reissued = true;
        }
        if (strlen($this->slconfig->getHudLinkCode()) > 12) {
            $reissued = true;
        }
        if (strlen($this->slconfig->getHttpInboundSecret()) > 12) {
            $reissued = true;
        }
        if ($reissued == true) {
            $reissueKeys = new Reissue();
            $reissueKeys->reissueKeys();
        }
        return $reissued;
    }

    protected function updateHudSettings(): void
    {
        $input = new InputFilter();
        $hudAllowDiscord = $input->postFilter("hudAllowDiscord", "bool");
        $hudDiscordLink = $input->postFilter("hudDiscordLink");
        $hudAllowGroup = $input->postFilter("hudAllowGroup", "bool");
        $hudGroupLink = $input->postFilter("hudGroupLink");
        $hudAllowDetails = $input->postFilter("hudAllowDetails", "bool");
        $hudAllowRenewal = $input->postFilter("hudAllowRenewal", "bool");
        if ($hudAllowRenewal == 1) {
            $hudAllowRenewal = $hudAllowDetails; // Unable to have renewal without details
        }
        $this->slconfig->setHudAllowDiscord($hudAllowDiscord);
        $this->slconfig->setHudDiscordLink($hudDiscordLink);
        $this->slconfig->setHudAllowGroup($hudAllowGroup);
        $this->slconfig->setHudGroupLink($hudGroupLink);
        $this->slconfig->setHudAllowDetails($hudAllowDetails);
        $this->slconfig->setHudAllowRenewal($hudAllowRenewal);
    }

    public function process(): void
    {
        $this->output->purgeCacheFile("current_timezone", false);

        $avatar = new Avatar();
        $timezone = new Timezones();
        $input = new InputFilter();

        $newResellersRate = $input->postFilter("newResellersRate", "integer");
        $newResellers = $input->postFilter("newResellers", "bool");
        $owneravuid = $input->postFilter("owneravuid");
        $ui_tweaks_clients_fulllist = $input->postFilter("ui_tweaks_clients_fulllist", "bool");
        $ui_tweaks_datatableItemsPerPage = $input->postFilter("ui_tweaks_datatableItemsPerPage", "integer");
        $apiDefaultEmail = $input->postFilter("apiDefaultEmail", "email");
        $displayTimezoneLink = $input->postFilter("displayTimezoneLink", "integer");

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

        $this->setSwapTag("redirect", "slconfig");
        if ($avatar->getId() != $this->slconfig->getOwnerAvatarLink()) {
            $this->slconfig->setOwnerAvatarLink($avatar->getId());
        }
        $this->slconfig->setNewResellers($newResellers);
        $this->slconfig->setNewResellersRate($newResellersRate);
        $this->slconfig->setClientsListMode($ui_tweaks_clients_fulllist);
        $this->slconfig->setDatatableItemsPerPage($ui_tweaks_datatableItemsPerPage);
        $this->slconfig->setDisplayTimezoneLink($displayTimezoneLink);
        $this->slconfig->setApiDefaultEmail($apiDefaultEmail);

        $this->updateHudSettings();
        $reissuedKeys = $this->forceReissue();
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
        if ($reissuedKeys == true) {
            $this->output->addSwapTagString("message", " [Forced key reissue due to bug]");
        }
    }
}
