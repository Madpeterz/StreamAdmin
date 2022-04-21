<?php

namespace App\Endpoint\Control\Server;

use App\Models\Server;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    protected Server $server;

    protected ?string $domain;
    protected ?string $controlPanelURL;

    protected function setup(): void
    {
        $this->server = new Server();
    }
    protected function formData(): bool
    {
        $this->domain = $this->input->post("domain")->checkStringLength(5, 100)->asString();
        if ($this->domain === null) {
            return false;
        }
        $this->controlPanelURL = $this->input->post("controlPanelURL")->checkStringLength(5, 100)->asString();
        if ($this->controlPanelURL === null) {
            return false;
        }
    }

    protected function createServer(): bool
    {
        if ($this->server->loadByField("domain", $this->domain)->status == true) {
            $this->failed("There is already a server assigned to that domain");
            return false;
        }
        $this->server = new Server();
        $this->server->setDomain($this->domain);
        $this->server->setControlPanelURL($this->controlPanelURL);
        $create_status = $this->server->createEntry();
        if ($create_status->status == false) {
            $this->failed(
                sprintf("Unable to create server: %1\$s", $create_status->message)
            );
            return false;
        }
        return true;
    }

    public function process(): void
    {
        $this->setup();
        if ($this->formData() == false) {
            $this->failed($this->input->getWhyFailed());
            return;
        } elseif ($this->createServer() == false) {
            return;
        }
        $this->ok("Server created");
        $this->setSwapTag("redirect", "server");
    }
}
