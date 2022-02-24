<?php

namespace App\Endpoint\Control\Slconfig;

use App\Models\Avatar;
use App\Models\Timezones;
use App\Framework\ViewAjax;

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

        $hudAllowDiscord = $input->postBool("hudAllowDiscord");
        $hudDiscordLink = $this->input->post("hudDiscordLink");
        if ($hudAllowDiscord == false) {
            $hudDiscordLink = null;
        }
        $hudAllowGroup = $input->postBool("hudAllowGroup");
        $hudGroupLink = $this->input->post("hudGroupLink");
        $hudDiscordLink = $this->input->post("hudDiscordLink");
        if ($hudAllowGroup == false) {
            $hudGroupLink = null;
        }
        if (strlen($hudGroupLink) == 0) {
            $hudAllowGroup = false;
        }
        if (strlen($hudDiscordLink) == 0) {
            $hudAllowDiscord = false;
        }
        $hudAllowDetails = $input->postBool("hudAllowDetails");
        $hudAllowRenewal = $input->postBool("hudAllowRenewal");
        if ($hudAllowRenewal == false) {
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
        $avatar = new Avatar();
        $timezone = new Timezones();


        $newResellersRate = $input->postInteger("newResellersRate");
        $newResellers = $input->postBool("newResellers");
        $owneravuid = $this->input->post("owneravuid", 8, 8);
        $ui_tweaks_clients_fulllist = $input->postBool("ui_tweaks_clients_fulllist");
        $ui_tweaks_datatableItemsPerPage = $input->postInteger("ui_tweaks_datatableItemsPerPage");
        $apiDefaultEmail = $input->postEmail("apiDefaultEmail");
        $displayTimezoneLink = $input->postInteger("displayTimezoneLink");
        $eventsAPI = $input->postBool("eventsAPI");

        if ($newResellersRate < 0) {
            $this->failed("newResellersRate must be 1 or more");
            return;
        }
        if ($newResellersRate > 100) {
            $this->failed("newResellersRate must be 100 or less");
            return;
        }
        if ($ui_tweaks_datatableItemsPerPage < 10) {
            $this->failed("Datatable entrys per page length must be 10 or more");
            return;
        }
        if ($ui_tweaks_datatableItemsPerPage > 200) {
            $this->failed("Datatable entrys per page must be 200 or less");
            return;
        }
        if (strlen($owneravuid) != 8) {
            $this->failed("Owner AV uid length must be 8");
            return;
        }
        if ($avatar->loadByField("avatarUid", $owneravuid) == false) {
            $this->failed("Unable to load avatar from uid");
            return;
        }
        if ($timezone->loadID($displayTimezoneLink) == false) {
            $this->failed("Timezone selected not supported");
            return;
        }
        if (strlen($apiDefaultEmail) < 7) {
            $this->failed("API default email address does not appear to be vaild");
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
        $this->slconfig->setEventsAPI($eventsAPI);

        $this->updateHudSettings();
        $reissuedKeys = $this->forceReissue();
        $update_status = $this->slconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf("Unable to update system config: %1\$s", $update_status["message"])
            );
            return;
        }

        $this->ok("System config updated");
        if ($reissuedKeys == true) {
            $this->output->addSwapTagString("message", " [Forced key reissue due to bug]");
        }
    }
}
