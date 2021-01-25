<?php

namespace App\Endpoint\Control\Server;

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
        if (strlen($controlPanelURL) < 5) {
            $this->setSwapTag("message", "controlpanel url length can not be less than 5");
            return;
        }
        if ($server->loadByField("domain", $domain) == true) {
            $this->setSwapTag("message", "There is already a server assigned to that domain");
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
        $server = new Server();
        $server->setDomain($domain);
        $server->setControlPanelURL($controlPanelURL);
        $server->setApiLink($apiLink);
        $server->setApiURL($apiURL);
        $server->setApiUsername($apiUsername);
        $server->setApiPassword($apiPassword);
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
        $create_status = $server->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create server: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Server created");
        $this->setSwapTag("redirect", "server");
    }
}
