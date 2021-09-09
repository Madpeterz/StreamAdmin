<?php

namespace App\Endpoint\Control\Server;

class Update extends Create
{

    public function process(): void
    {
        $this->setup();
        $this->formData();
        if ($this->loadServer() == false) {
            return;
        } elseif ($this->tests() == false) {
            return;
        } elseif ($this->extendedDomainCheck() == false) {
            return;
        } elseif ($this->updateServer() == false) {
            return;
        }
        $this->ok("Server updated");
        $this->setSwapTag("redirect", "server");
    }

    protected function loadServer(): bool
    {
        if ($this->server->loadID($this->page) == false) {
            $this->failed("Unable to find server");
            $this->setSwapTag("redirect", "server");
            return false;
        }
        return true;
    }

    protected function updateServer(): bool
    {
        $this->setupServer();
        $update_status = $this->server->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update server: %1\$s", $update_status["message"])
            );
            return false;
        }
        return true;
    }

    protected function extendedDomainCheck(): bool
    {
        $whereConfig = [
            "fields" => ["domain"],
            "values" => [$this->domain],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($this->server->getTable(), $whereConfig);
        $expected_count = 0;
        if ($this->server->getDomain() == $this->domain) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag(
                "message",
                "Unable to check if there is a server assigned to domain already"
            );
            return false;
        }
        if ($count_check["count"] != $expected_count) {
            $this->failed("There is already a server with that domain");
            return false;
        }
        return true;
    }
}
