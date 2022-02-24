<?php

namespace App\Endpoint\Control\Server;

use App\Models\Sets\ApisSet;
use App\Models\Server;
use App\Framework\ViewAjax;

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
        $this->domain = $this->input->post("domain");
        $this->controlPanelURL = $this->input->post("controlPanelURL");
        $this->apiLink = $this->input->postInteger("apiLink");
        $this->apiURL = $this->input->post("apiURL");
        $this->apiUsername = $this->input->post("apiUsername");
        $this->apiPassword = $this->input->post("apiPassword");
        $this->optPasswordReset = $this->input->postBool("optPasswordReset");
        $this->optAutodjNext = $this->input->postBool("optAutodjNext");
        $this->optToggleAutodj = $this->input->postBool("optToggleAutodj");
        $this->eventEnableStart = $this->input->postBool("eventEnableStart");
        $this->eventDisableExpire = $this->input->postBool("eventDisableExpire");
        $this->eventDisableRevoke = $this->input->postBool("eventDisableRevoke");
        $this->eventResetPasswordRevoke = $this->input->postBool("eventResetPasswordRevoke");
        $this->eventEnableRenew = $this->input->postBool("eventEnableRenew");
        $this->optToggleStatus = $this->input->postBool("optToggleStatus");
        $this->eventStartSyncUsername = $this->input->postBool("eventStartSyncUsername");
        $this->apiServerStatus = $this->input->postBool("apiServerStatus");
        $this->eventClearDjs = $this->input->postBool("eventClearDjs");
        $this->eventRevokeResetUsername = $this->input->postBool("eventRevokeResetUsername");
        $this->eventRecreateRevoke = $this->input->postBool("eventRecreateRevoke");
        $this->apiSyncAccounts = $this->input->postBool("apiSyncAccounts");
        $this->eventCreateStream = $this->input->postBool("eventCreateStream");
        $this->eventUpdateStream = $this->input->postBool("eventUpdateStream");
    }

    protected function tests(): bool
    {
        $yesno_array = [false,true];
        if (strlen($this->domain) > 100) {
            $this->failed("Domain length can not be more than 200");
            return false;
        } elseif (strlen($this->domain) < 5) {
            $this->failed("Domain length can not be less than 5");
            return false;
        } elseif (strlen($this->controlPanelURL) < 5) {
            $this->failed("controlpanel url length can not be less than 5");
            return false;
        } elseif (in_array($this->apiLink, $this->apis->getAllIds()) == false) {
            $this->failed("Not a supported api");
            return false;
        } elseif (in_array($this->optPasswordReset, $yesno_array) == false) {
            $this->failed("optPasswordReset not vaild");
            return false;
        } elseif (in_array($this->optAutodjNext, $yesno_array) == false) {
            $this->failed("optAutodjNext not vaild");
            return false;
        } elseif (in_array($this->optToggleAutodj, $yesno_array) == false) {
            $this->failed("optToggleAutodj not vaild");
            return false;
        } elseif (in_array($this->eventEnableStart, $yesno_array) == false) {
            $this->failed("eventEnableStart not vaild");
            return false;
        } elseif (in_array($this->eventDisableExpire, $yesno_array) == false) {
            $this->failed("eventDisableExpire not vaild");
            return false;
        } elseif (in_array($this->eventDisableRevoke, $yesno_array) == false) {
            $this->failed("eventDisableRevoke not vaild");
            return false;
        } elseif (in_array($this->eventResetPasswordRevoke, $yesno_array) == false) {
            $this->failed("eventResetPasswordRevoke not vaild");
            return false;
        } elseif (in_array($this->eventEnableRenew, $yesno_array) == false) {
            $this->failed("eventEnableRenew not vaild");
            return false;
        } elseif (in_array($this->optToggleStatus, $yesno_array) == false) {
            $this->failed("optToggleStatus not vaild");
            return false;
        } elseif (in_array($this->eventStartSyncUsername, $yesno_array) == false) {
            $this->failed("eventStartSyncUsername not vaild");
            return false;
        } elseif (in_array($this->apiServerStatus, $yesno_array) == false) {
            $this->failed("apiServerStatus not vaild");
            return false;
        } elseif (in_array($this->eventClearDjs, $yesno_array) == false) {
            $this->failed("eventClearDjs not vaild");
            return false;
        } elseif (in_array($this->eventRevokeResetUsername, $yesno_array) == false) {
            $this->failed("eventRevokeResetUsername not vaild");
            return false;
        } elseif (in_array($this->eventRecreateRevoke, $yesno_array) == false) {
            $this->failed("eventRecreateRevoke not vaild");
            return false;
        } elseif (in_array($this->apiSyncAccounts, $yesno_array) == false) {
            $this->failed("apiSyncAccounts not vaild");
            return false;
        } elseif (in_array($this->eventCreateStream, $yesno_array) == false) {
            $this->failed("eventCreateStream not vaild");
            return false;
        } elseif (in_array($this->eventUpdateStream, $yesno_array) == false) {
            $this->failed("eventUpdateStream not vaild");
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
            $this->failed("There is already a server assigned to that domain");
            return false;
        }
        $this->server = new Server();
        $this->setupServer();
        $create_status = $this->server->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
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
        $this->ok("Server created");
        $this->setSwapTag("redirect", "server");
    }
}
