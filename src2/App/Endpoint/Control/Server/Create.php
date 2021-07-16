<?php

namespace App\Endpoint\Control\Server;

use App\R7\Set\ApisSet;
use App\R7\Model\Server;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    protected ApisSet $apis;
    protected Server $server;
    protected InputFilter $input;

    protected ?string $domain;
    protected ?string $controlPanelURL;
    protected ?int $apiLink;
    protected ?string $apiURL;
    protected ?string $apiUsername;
    protected ?string $apiPassword;
    protected ?int $optPasswordReset;
    protected ?bool $optAutodjNext;
    protected ?bool $optToggleAutodj;
    protected ?bool $eventEnableStart;
    protected ?bool $eventDisableExpire;
    protected ?bool $eventDisableRevoke;
    protected ?bool $eventResetPasswordRevoke;
    protected ?bool $eventEnableRenew;
    protected ?bool $optToggleStatus;
    protected ?bool $eventStartSyncUsername;
    protected ?bool $apiServerStatus;
    protected ?bool $eventClearDjs;
    protected ?bool $eventRevokeResetUsername;
    protected ?bool $eventRecreateRevoke;
    protected ?bool $apiSyncAccounts;
    protected ?bool $eventCreateStream;
    protected ?bool $eventUpdateStream;


    protected function setup(): void
    {
        $this->apis = new ApisSet();
        $this->server = new Server();
        $this->input = new InputFilter();
        $this->apis->loadAll();
    }
    protected function formData(): void
    {
        $this->domain = $this->input->postFilter("domain");
        $this->controlPanelURL = $this->input->postFilter("controlPanelURL");
        $this->apiLink = $this->input->postFilter("apiLink", "integer");
        $this->apiURL = $this->input->postFilter("apiURL");
        $this->apiUsername = $this->input->postFilter("apiUsername");
        $this->apiPassword = $this->input->postFilter("apiPassword");
        $this->optPasswordReset = $this->input->postFilter("optPasswordReset", "bool");
        $this->optAutodjNext = $this->input->postFilter("optAutodjNext", "bool");
        $this->optToggleAutodj = $this->input->postFilter("optToggleAutodj", "bool");
        $this->eventEnableStart = $this->input->postFilter("eventEnableStart", "bool");
        $this->eventDisableExpire = $this->input->postFilter("eventDisableExpire", "bool");
        $this->eventDisableRevoke = $this->input->postFilter("eventDisableRevoke", "bool");
        $this->eventResetPasswordRevoke = $this->input->postFilter("eventResetPasswordRevoke", "bool");
        $this->eventEnableRenew = $this->input->postFilter("eventEnableRenew", "bool");
        $this->optToggleStatus = $this->input->postFilter("optToggleStatus", "bool");
        $this->eventStartSyncUsername = $this->input->postFilter("eventStartSyncUsername", "bool");
        $this->apiServerStatus = $this->input->postFilter("apiServerStatus", "bool");
        $this->eventClearDjs = $this->input->postFilter("eventClearDjs", "bool");
        $this->eventRevokeResetUsername = $this->input->postFilter("eventRevokeResetUsername", "bool");
        $this->eventRecreateRevoke = $this->input->postFilter("eventRecreateRevoke", "bool");
        $this->apiSyncAccounts = $this->input->postFilter("apiSyncAccounts", "bool");
        $this->eventCreateStream = $this->input->postFilter("eventCreateStream", "bool");
        $this->eventUpdateStream = $this->input->postFilter("eventUpdateStream", "bool");
    }

    protected function tests(): bool
    {
        $yesno_array = [false,true];
        if (strlen($this->domain) > 100) {
            $this->setSwapTag("message", "Domain length can not be more than 200");
            return false;
        } elseif (strlen($this->domain) < 5) {
            $this->setSwapTag("message", "Domain length can not be less than 5");
            return false;
        } elseif (strlen($this->controlPanelURL) < 5) {
            $this->setSwapTag("message", "controlpanel url length can not be less than 5");
            return false;
        } elseif (in_array($this->apiLink, $this->apis->getAllIds()) == false) {
            $this->setSwapTag("message", "Not a supported api");
            return false;
        } elseif (in_array($this->optPasswordReset, $yesno_array) == false) {
            $this->setSwapTag("message", "optPasswordReset not vaild");
            return false;
        } elseif (in_array($this->optAutodjNext, $yesno_array) == false) {
            $this->setSwapTag("message", "optAutodjNext not vaild");
            return false;
        } elseif (in_array($this->optToggleAutodj, $yesno_array) == false) {
            $this->setSwapTag("message", "optToggleAutodj not vaild");
            return false;
        } elseif (in_array($this->eventEnableStart, $yesno_array) == false) {
            $this->setSwapTag("message", "eventEnableStart not vaild");
            return false;
        } elseif (in_array($this->eventDisableExpire, $yesno_array) == false) {
            $this->setSwapTag("message", "eventDisableExpire not vaild");
            return false;
        } elseif (in_array($this->eventDisableRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventDisableRevoke not vaild");
            return false;
        } elseif (in_array($this->eventResetPasswordRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventResetPasswordRevoke not vaild");
            return false;
        } elseif (in_array($this->eventEnableRenew, $yesno_array) == false) {
            $this->setSwapTag("message", "eventEnableRenew not vaild");
            return false;
        } elseif (in_array($this->optToggleStatus, $yesno_array) == false) {
            $this->setSwapTag("message", "optToggleStatus not vaild");
            return false;
        } elseif (in_array($this->eventStartSyncUsername, $yesno_array) == false) {
            $this->setSwapTag("message", "eventStartSyncUsername not vaild");
            return false;
        } elseif (in_array($this->apiServerStatus, $yesno_array) == false) {
            $this->setSwapTag("message", "apiServerStatus not vaild");
            return false;
        } elseif (in_array($this->eventClearDjs, $yesno_array) == false) {
            $this->setSwapTag("message", "eventClearDjs not vaild");
            return false;
        } elseif (in_array($this->eventRevokeResetUsername, $yesno_array) == false) {
            $this->setSwapTag("message", "eventRevokeResetUsername not vaild");
            return false;
        } elseif (in_array($this->eventRecreateRevoke, $yesno_array) == false) {
            $this->setSwapTag("message", "eventRecreateRevoke not vaild");
            return false;
        } elseif (in_array($this->apiSyncAccounts, $yesno_array) == false) {
            $this->setSwapTag("message", "apiSyncAccounts not vaild");
            return false;
        } elseif (in_array($this->eventCreateStream, $yesno_array) == false) {
            $this->setSwapTag("message", "eventCreateStream not vaild");
            return false;
        } elseif (in_array($this->eventUpdateStream, $yesno_array) == false) {
            $this->setSwapTag("message", "eventUpdateStream not vaild");
            return false;
        }
        return true;
    }

    protected function setupServer(): void
    {
        $this->server->setDomain($this->domain);
        $this->server->setControlPanelURL($this->controlPanelURL);
        $this->server->setApiLink($this->apiLink);
        $this->server->setApiURL($this->apiURL);
        $this->server->setApiUsername($this->apiUsername);
        if ($this->apiPassword != "NoChange") {
            $this->server->setApiPassword($this->apiPassword);
        } elseif ($this->server->getApiPassword() == "") {
            $this->server->setApiPassword($this->apiPassword);
        }
        $this->server->setOptPasswordReset($this->optPasswordReset);
        $this->server->setOptAutodjNext($this->optAutodjNext);
        $this->server->setOptToggleAutodj($this->optToggleAutodj);
        $this->server->setEventEnableStart($this->eventEnableStart);
        $this->server->setEventDisableExpire($this->eventDisableExpire);
        $this->server->setEventDisableRevoke($this->eventDisableRevoke);
        $this->server->setEventResetPasswordRevoke($this->eventResetPasswordRevoke);
        $this->server->setEventEnableRenew($this->eventEnableRenew);
        $this->server->setOptToggleStatus($this->optToggleStatus);
        $this->server->setEventStartSyncUsername($this->eventStartSyncUsername);
        $this->server->setApiServerStatus($this->apiServerStatus);
        $this->server->setEventClearDjs($this->eventClearDjs);
        $this->server->setEventRevokeResetUsername($this->eventRevokeResetUsername);
        $this->server->setEventRecreateRevoke($this->eventRecreateRevoke);
        $this->server->setApiSyncAccounts($this->apiSyncAccounts);
        $this->server->setEventCreateStream($this->eventCreateStream);
        $this->server->setEventUpdateStream($this->eventUpdateStream);
    }

    protected function createServer(): bool
    {
        if ($this->server->loadByField("domain", $this->domain) == true) {
            $this->setSwapTag("message", "There is already a server assigned to that domain");
            return false;
        }
        $this->server = new Server();
        $this->setupServer();
        $create_status = $this->server->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create server: %1\$s", $create_status["message"])
            );
            return false;
        }
        return true;
    }

    public function process(): void
    {
        $this->setup();
        $this->formData();
        if ($this->tests() == false) {
            return;
        } elseif ($this->createServer() == false) {
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Server created");
        $this->setSwapTag("redirect", "server");
    }
}
