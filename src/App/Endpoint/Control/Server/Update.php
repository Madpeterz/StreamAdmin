<?php

namespace App\Endpoint\Control\Server;

use App\Models\Set\ServerSet;

class Update extends Create
{
    public function process(): void
    {
        $this->setup();
        $this->formData();
        if ($this->loadServer() == false) {
            return;
        } elseif ($this->extendedDomainCheck() == false) {
            return;
        } elseif ($this->updateServer() == false) {
            return;
        }
        $this->redirectWithMessage("Server updated");
    }

    protected function loadServer(): bool
    {
        if ($this->server->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find server");
            $this->setSwapTag("redirect", "server");
            return false;
        }
        return true;
    }

    protected function updateServer(): bool
    {
        $oldvalues = $this->server->objectToValueArray();
        $this->server->_Domain = $this->domain;
        $this->server->_ControlPanelURL = $this->controlPanelURL;
        $this->server->_Ipaddress = $this->ipaddress;
        $update_status = $this->server->updateEntry();
        if ($update_status->status == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update server: %1\$s", $update_status->message)
            );
            return false;
        }
        $this->createMultiAudit(
            $this->server->_Id,
            $this->server->getFields(),
            $oldvalues,
            $this->server->objectToValueArray()
        );
        return true;
    }

    protected function extendedDomainCheck(): bool
    {
        $expected_count = 0;
        if ($this->server->_Domain == $this->domain) {
            $expected_count = 1;
        }
        $whereConfig = [
            "fields" => ["domain"],
            "values" => [$this->domain],
        ];
        $serverSet = new ServerSet();
        $count = $serverSet->countInDB($whereConfig);
        if ($count->status == false) {
            $this->failed("Unable to check for domain usage");
            return false;
        }
        if ($count->items != $expected_count) {
            $this->failed("There is already a server assigned to that domain");
            return false;
        }
        $expected_count = 0;
        if ($this->server->_Ipaddress == $this->ipaddress) {
            $expected_count = 1;
        }
        $whereConfig = [
            "fields" => ["ipaddress"],
            "values" => [$this->ipaddress],
        ];
        $count = $serverSet->countInDB($whereConfig);
        if ($count->status == false) {
            $this->failed("Unable to check for ip usage");
            return false;
        }
        if ($count->items != $expected_count) {
            $this->failed("There is already a server assigned to that ip");
            return false;
        }
        return true;
    }
}
