<?php

namespace App\Endpoints\Control\Server;

use App\Models\ApisSet;
use App\Models\Server;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $apis = new ApisSet();
        $server = new Server();
        $input = new InputFilter();

        $apis->loadAll();

        $domain = $input->postFilter("domain");
        $controlpanel_url = $input->postFilter("controlpanel_url");
        $apilink = $input->postFilter("apilink", "integer");
        $api_url = $input->postFilter("api_url");
        $api_username = $input->postFilter("api_username");
        $api_password = $input->postFilter("api_password");
        $opt_password_reset = $input->postFilter("opt_password_reset", "integer");
        $opt_autodj_next = $input->postFilter("opt_autodj_next", "integer");
        $opt_toggle_autodj = $input->postFilter("opt_toggle_autodj", "integer");
        $event_enable_start = $input->postFilter("event_enable_start", "integer");
        $event_disable_expire = $input->postFilter("event_disable_expire", "integer");
        $event_disable_revoke = $input->postFilter("event_disable_revoke", "integer");
        $event_reset_password_revoke = $input->postFilter("event_reset_password_revoke", "integer");
        $event_enable_renew = $input->postFilter("event_enable_renew", "integer");
        $opt_toggle_status = $input->postFilter("opt_toggle_status", "integer");
        $event_start_sync_username = $input->postFilter("event_start_sync_username", "integer");
        $api_serverstatus = $input->postFilter("api_serverstatus", "integer");
        $event_clear_djs = $input->postFilter("event_clear_djs", "integer");
        $event_revoke_reset_username = $input->postFilter("event_revoke_reset_username", "integer");
        $event_recreate_revoke = $input->postFilter("event_recreate_revoke", "integer");
        $api_sync_accounts = $input->postFilter("api_sync_accounts", "integer");
        $event_create_stream = $input->postFilter("event_create_stream", "integer");
        $event_update_stream = $input->postFilter("event_update_stream", "integer");

        $failed_on = "";
        $this->setSwapTag("redirect", "");
        $yesno_array = [0,1];
        if (strlen($domain) > 100) {
            $this->setSwapTag("message", "Domain length can not be more than 200");
            return;
        }
        if (strlen($domain) < 5) {
            $this->setSwapTag("message", "Domain length can not be less than 5");
            return;
        }
        if (strlen($controlpanel_url) < 5) {
            $this->setSwapTag("message", "controlpanel url length can not be less than 5");
            return;
        }
        if ($server->loadByField("domain", $domain) == true) {
            $this->setSwapTag("message", "There is already a server assigned to that domain");
            return;
        }
        if (in_array($apilink, $apis->getAllIds()) == false) {
            $this->setSwapTag("message", "Not a supported api");
            return;
        }
        if (in_array($opt_password_reset, $yesno_array) == false) {
            $this->setSwapTag("message", "opt_password_reset not vaild");
            return;
        }
        if (in_array($opt_autodj_next, $yesno_array) == false) {
            $this->setSwapTag("message", "opt_autodj_next not vaild");
            return;
        }
        if (in_array($opt_toggle_autodj, $yesno_array) == false) {
            $this->setSwapTag("message", "opt_toggle_autodj not vaild");
            return;
        }
        if (in_array($event_enable_start, $yesno_array) == false) {
            $this->setSwapTag("message", "event_enable_start not vaild");
            return;
        }
        if (in_array($event_disable_expire, $yesno_array) == false) {
            $this->setSwapTag("message", "event_disable_expire not vaild");
            return;
        }
        if (in_array($event_disable_revoke, $yesno_array) == false) {
            $this->setSwapTag("message", "event_disable_revoke not vaild");
            return;
        }
        if (in_array($event_reset_password_revoke, $yesno_array) == false) {
            $this->setSwapTag("message", "event_reset_password_revoke not vaild");
            return;
        }
        if (in_array($event_enable_renew, $yesno_array) == false) {
            $this->setSwapTag("message", "event_enable_renew not vaild");
            return;
        }
        if (in_array($opt_toggle_status, $yesno_array) == false) {
            $this->setSwapTag("message", "opt_toggle_status not vaild");
            return;
        }
        if (in_array($event_start_sync_username, $yesno_array) == false) {
            $this->setSwapTag("message", "event_start_sync_username not vaild");
            return;
        }
        if (in_array($api_serverstatus, $yesno_array) == false) {
            $this->setSwapTag("message", "api_serverstatus not vaild");
            return;
        }
        if (in_array($event_clear_djs, $yesno_array) == false) {
            $this->setSwapTag("message", "event_clear_djs not vaild");
            return;
        }
        if (in_array($event_revoke_reset_username, $yesno_array) == false) {
            $this->setSwapTag("message", "event_revoke_reset_username not vaild");
            return;
        }
        if (in_array($event_recreate_revoke, $yesno_array) == false) {
            $this->setSwapTag("message", "event_recreate_revoke not vaild");
            return;
        }
        if (in_array($api_sync_accounts, $yesno_array) == false) {
            $this->setSwapTag("message", "api_sync_accounts not vaild");
            return;
        }
        if (in_array($event_create_stream, $yesno_array) == false) {
            $this->setSwapTag("message", "event_create_stream not vaild");
            return;
        }
        if (in_array($event_update_stream, $yesno_array) == false) {
            $this->setSwapTag("message", "event_update_stream not vaild");
            return;
        }
        $server = new Server();
        $server->setDomain($domain);
        $server->setControlpanel_url($controlpanel_url);
        $server->setApilink($apilink);
        $server->setApi_url($api_url);
        $server->setApi_username($api_username);
        $server->setApi_password($api_password);
        $server->setOpt_password_reset($opt_password_reset);
        $server->setOpt_autodj_next($opt_autodj_next);
        $server->setOpt_toggle_autodj($opt_toggle_autodj);
        $server->setEvent_enable_start($event_enable_start);
        $server->setEvent_disable_expire($event_disable_expire);
        $server->setEvent_disable_revoke($event_disable_revoke);
        $server->setEvent_reset_password_revoke($event_reset_password_revoke);
        $server->setEvent_enable_renew($event_enable_renew);
        $server->setOpt_toggle_status($opt_toggle_status);
        $server->setEvent_start_sync_username($event_start_sync_username);
        $server->setApi_serverstatus($api_serverstatus);
        $server->setEvent_clear_djs($event_clear_djs);
        $server->setEvent_revoke_reset_username($event_revoke_reset_username);
        $server->setEvent_recreate_revoke($event_recreate_revoke);
        $server->setApi_sync_accounts($api_sync_accounts);
        $server->setEvent_create_stream($event_create_stream);
        $server->setEvent_update_stream($event_update_stream);
        $create_status = $server->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create server: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Server created");
        $this->setSwapTag("redirect", "server");
    }
}
