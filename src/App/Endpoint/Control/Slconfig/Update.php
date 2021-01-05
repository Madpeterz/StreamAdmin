<?php

namespace App\Endpoints\Control\Slconfig;

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

        $sllinkcode = $input->postFilter("sllinkcode");
        $httpcode = $input->postFilter("httpcode");
        $publiclinkcode = $input->postFilter("publiclinkcode");
        $new_resellers_rate = $input->postFilter("new_resellers_rate", "integer");
        $new_resellers = $input->postFilter("new_resellers", "bool");
        $event_storage = $input->postFilter("event_storage", "bool");
        $owneravuid = $input->postFilter("owneravuid");
        $ui_tweaks_clients_fulllist = $input->postFilter("ui_tweaks_clients_fulllist", "bool");
        $ui_tweaks_datatable_itemsperpage = $input->postFilter("ui_tweaks_datatable_itemsperpage", "integer");
        $api_default_email = $input->postFilter("api_default_email", "email");
        $displaytimezonelink = $input->postFilter("displaytimezonelink", "integer");

        if (strlen($sllinkcode) < 5) {
            $this->output->setSwapTagString("message", "sllinkcode length must be 5 or longer");
            return;
        }
        if (strlen($sllinkcode) > 10) {
            $this->output->setSwapTagString("message", "sllinkcode length must be 10 or less");
            return;
        }
        if (strlen($httpcode) < 5) {
            $this->output->setSwapTagString("message", "httpcode length must be 5 or longer");
            return;
        }
        if (strlen($httpcode) > 30) {
            $this->output->setSwapTagString("message", "httpcode length must be 30 or less");
            return;
        }
        if ($new_resellers_rate < 0) {
            $this->output->setSwapTagString("message", "new_resellers_rate must be 1 or more");
            return;
        }
        if ($new_resellers_rate > 100) {
            $this->output->setSwapTagString("message", "new_resellers_rate must be 100 or less");
            return;
        }
        if ($ui_tweaks_datatable_itemsperpage < 10) {
            $this->output->setSwapTagString("message", "Datatable entrys per page length must be 10 or more");
            return;
        }
        if ($ui_tweaks_datatable_itemsperpage > 200) {
            $this->output->setSwapTagString("message", "Datatable entrys per page must be 200 or less");
            return;
        }
        if (strlen($owneravuid) != 8) {
            $this->output->setSwapTagString("message", "Owner AV uid length must be 8");
            return;
        }
        if ($avatar->loadByField("avatar_uid", $owneravuid) == false) {
            $this->output->setSwapTagString("message", "Unable to load avatar from uid");
            return;
        }
        if ($timezone->loadID($displaytimezonelink) == false) {
            $this->output->setSwapTagString("message", "Timezone selected not supported");
            return;
        }
        if (strlen($api_default_email) < 7) {
            $this->output->setSwapTagString("message", "API default email address does not appear to be vaild");
            return;
        }
        if (strlen($publiclinkcode) < 6) {
            $this->output->setSwapTagString("message", "Public link code min length is 6");
            return;
        }
        if (strlen($publiclinkcode) > 12) {
            $this->output->setSwapTagString("message", "Public link code max length is 12");
            return;
        }

        $this->output->setSwapTagString("redirect", "slconfig");
        if ($avatar->getId() != $this->slconfig->getOwner_av()) {
            $this->slconfig->setOwner_av($avatar->getId());
        }
        $this->slconfig->setSllinkcode($sllinkcode);
        $this->slconfig->setPubliclinkcode($publiclinkcode);
        $this->slconfig->setHttp_inbound_secret($httpcode);
        $this->slconfig->setNew_resellers($new_resellers);
        $this->slconfig->setNew_resellers_rate($new_resellers_rate);
        $this->slconfig->setEventstorage($event_storage);
        $this->slconfig->setClients_list_mode($ui_tweaks_clients_fulllist);
        $this->slconfig->setDatatable_itemsperpage($ui_tweaks_datatable_itemsperpage);
        $this->slconfig->setDisplaytimezonelink($displaytimezonelink);
        $this->slconfig->setApi_default_email($api_default_email);
        if ($this->session->getOwnerLevel() == 1) {
            $smtp_from = $input->postFilter("smtp_from");
            $smtp_reply = $input->postFilter("smtp_reply");
            $smtp_host = $input->postFilter("smtp_host");
            $smtp_user = $input->postFilter("smtp_user");
            $smtp_code = $input->postFilter("smtp_code");
            $smtp_port = $input->postFilter("smtp_port");
            // missing tests here :P
            $this->slconfig->setSmtp_host($smtp_host);
            $this->slconfig->setSmtp_port($smtp_port);
            if ($smtp_user != "skip") {
                $this->slconfig->setSmtp_username($smtp_user);
            }
            if ($smtp_code != "skip") {
                $this->slconfig->setSmtp_accesscode($smtp_code);
            }
            $this->slconfig->setSmtp_from($smtp_from);
            $this->slconfig->setSmtp_replyto($smtp_reply);
        }
            $update_status = $this->slconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to update system config: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "system config updated");
    }
}
