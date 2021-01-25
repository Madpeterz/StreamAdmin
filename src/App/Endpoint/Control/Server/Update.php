<?php

namespace App\Endpoint\Control\Server;

use App\Models\ApisSet;
use App\Models\Server;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $apis = new ApisSet();
        $server = new Server();
        $input = new InputFilter();

        $apis->loadAll();

        $domain = $input->postFilter("domain");
        $controlPanelURL = $input->postFilter("controlPanelURL");
        $apiLink = $input->postFilter("apiLink", "integer");
        $apiURL = $input->postFilter("apiURL");
        $apiUsername = $input->postFilter("apiUsername");
        $apiPassword = $input->postFilter("apiPassword");
        $optPasswordReset = $input->postFilter("optPasswordReset", "integer");
        $optAutodjNext = $input->postFilter("optAutodjNext", "integer");
        $optToggleAutodj = $input->postFilter("optToggleAutodj", "integer");
        $eventEnableStart = $input->postFilter("eventEnableStart", "integer");
        $eventDisableExpire = $input->postFilter("eventDisableExpire", "integer");
        $eventDisableRevoke = $input->postFilter("eventDisableRevoke", "integer");
        $eventResetPasswordRevoke = $input->postFilter("eventResetPasswordRevoke", "integer");
        $eventEnableRenew = $input->postFilter("eventEnableRenew", "integer");
        $optToggleStatus = $input->postFilter("optToggleStatus", "integer");
        $eventStartSyncUsername = $input->postFilter("eventStartSyncUsername", "integer");
        $apiServerStatus = $input->postFilter("apiServerStatus", "integer");
        $eventClearDjs = $input->postFilter("eventClearDjs", "integer");
        $eventRevokeResetUsername = $input->postFilter("eventRevokeResetUsername", "integer");
        $eventRecreateRevoke = $input->postFilter("eventRecreateRevoke", "integer");
        $apiSyncAccounts = $input->postFilter("apiSyncAccounts", "integer");
        $eventCreateStream = $input->postFilter("eventCreateStream", "integer");
        $eventUpdateStream = $input->postFilter("eventUpdateStream", "integer");

        $yesno_array = [0,1];
        if (strlen($domain) > 100) {
            $this->setSwapTag("message", "Domain length can not be more than 200");
            return;
        }
        if (strlen($domain) < 5) {
            $this->setSwapTag("message", "Domain length can not be less than 5");
            return;
        }
        if (strlen($controlPanelURL) < 5) {
            $this->setSwapTag("message", "controlpanel url length can not be less than 5");
            return;
        }
        if (in_array($apiLink, $apis->getAllIds()) == false) {
            $this->setSwapTag("message", "Not a supported api");
            return;
        }
        if (in_array($optPasswordReset, $yesno_array) == false) {
            $this->setSwapTag("message", "optPasswordReset not vaild");
            return;
        }
        if (in_array($optAutodjNext, $yesno_array) == false) {
            $this->setSwapTag("message", "optAutodjNext not vaild");
            return;
        }
        if (in_array($optToggleAutodj, $yesno_array) == false) {
            $this->setSwapTag("message", "optToggleAutodj not vaild");
            return;
        }
        if (in_array($eventEnableStart, $yesno_array) == false) {
            $this->setSwapTag("message", "eventEnableStart not vaild");
            return;
        }
        if (in_array($eventDisableExpire, $yesno_array) == false) {
            $this->setSwapTag("message", "eventDisableExpire not vaild");
            return;
        }
        if (in_array($eventDisableRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventDisableRevoke not vaild");
            return;
        }
        if (in_array($eventResetPasswordRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventResetPasswordRevoke not vaild");
            return;
        }
        if (in_array($eventEnableRenew, $yesno_array) == false) {
            $this->setSwapTag("message", "eventEnableRenew not vaild");
            return;
        }
        if (in_array($optToggleStatus, $yesno_array) == false) {
            $this->setSwapTag("message", "optToggleStatus not vaild");
            return;
        }
        if (in_array($eventStartSyncUsername, $yesno_array) == false) {
            $this->setSwapTag("message", "eventStartSyncUsername not vaild");
            return;
        }
        if (in_array($apiServerStatus, $yesno_array) == false) {
            $this->setSwapTag("message", "apiServerStatus not vaild");
            return;
        }
        if (in_array($eventClearDjs, $yesno_array) == false) {
            $this->setSwapTag("message", "eventClearDjs not vaild");
            return;
        }
        if (in_array($eventRevokeResetUsername, $yesno_array) == false) {
            $this->setSwapTag("message", "eventRevokeResetUsername not vaild");
            return;
        }
        if (in_array($eventRecreateRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventRecreateRevoke not vaild");
            return;
        }
        if (in_array($apiSyncAccounts, $yesno_array) == false) {
            $this->setSwapTag("message", "apiSyncAccounts not vaild");
            return;
        }
        if (in_array($eventCreateStream, $yesno_array) == false) {
            $this->setSwapTag("message", "eventCreateStream not vaild");
            return;
        }
        if (in_array($eventUpdateStream, $yesno_array) == false) {
            $this->setSwapTag("message", "eventUpdateStream not vaild");
            return;
        }

        if ($server->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find server");
            $this->setSwapTag("redirect", "server");
            return;
        }
        $whereConfig = [
            "fields" => ["domain"],
            "values" => [$domain],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($server->getTable(), $whereConfig);
        $expected_count = 0;
        if ($server->getDomain() == $domain) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag(
                "message",
                "Unable to check if there is a server assigned to domain already"
            );
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->setSwapTag("message", "There is already a server with that domain");
            return;
        }
        $server->setDomain($domain);
        $server->setControlPanelURL($controlPanelURL);
        $server->setApiLink($apiLink);
        $server->setApiURL($apiURL);
        $server->setApiUsername($apiUsername);
        if ($apiPassword != "NoChange") {
            $server->setApiPassword($apiPassword);
        }
        $server->setOptPasswordReset($optPasswordReset);
        $server->setOptAutodjNext($optAutodjNext);
        $server->setOptToggleAutodj($optToggleAutodj);
        $server->setEventEnableStart($eventEnableStart);
        $server->setEventDisableExpire($eventDisableExpire);
        $server->setEventDisableRevoke($eventDisableRevoke);
        $server->setEventResetPasswordRevoke($eventResetPasswordRevoke);
        $server->setEventEnableRenew($eventEnableRenew);
        $server->setOptToggleStatus($optToggleStatus);
        $server->setEventStartSyncUsername($eventStartSyncUsername);
        $server->setApiServerStatus($apiServerStatus);
        $server->setEventClearDjs($eventClearDjs);
        $server->setEventRevokeResetUsername($eventRevokeResetUsername);
        $server->setEventRecreateRevoke($eventRecreateRevoke);
        $server->setApiSyncAccounts($apiSyncAccounts);
        $server->setEventCreateStream($eventCreateStream);
        $server->setEventUpdateStream($eventUpdateStream);

        $update_status = $server->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update server: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Server updated");
        $this->setSwapTag("redirect", "server");
    }
}
