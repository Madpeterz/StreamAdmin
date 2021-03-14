<?php

namespace App\Helpers\ServerApi;

use App\R7\Model\Apis;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;

abstract class LoadServerApiHelper extends GetServerApiHelper
{
    protected function loadApi(): bool
    {
        $api = new Apis();
        $processed = false;
        if ($api->loadID($this->server->getApiLink()) == true) {
            if ($api->getId() > 1) {
                $this->api_config = $api;
                $serverApiName = "App\\MediaServer\\" . ucfirst($api->getName());
                if (class_exists($serverApiName) == true) {
                    $this->serverApi = new $serverApiName($this->stream, $this->server, $this->package);
                    $this->message = "server API loaded";
                    return true;
                } else {
                    $this->message = "unable to load server API";
                }
            } else {
                $this->message = "Server does not support API commands";
            }
        } else {
            $this->message = "Unable to load api config";
        }
        return false;
    }
    protected function loadRental(): bool
    {
        $rental = new Rental();
        if ($rental->loadByField("streamLink", $this->stream->getId()) == true) {
            $this->rental = $rental;
            $this->message = "Rental loaded";
            return true;
        }
        $this->message = "Unable to load rental";
        return false;
    }
    protected function loadPackage(): bool
    {
        $package = new Package();
        if ($package->loadID($this->stream->getPackageLink()) == true) {
            $this->package = $package;
            $this->message = "Package loaded";
            return true;
        }
        $this->message = "Unable to load package";
        return false;
    }
    protected function loadServer(): bool
    {
        $server = new Server();
        if ($server->loadID($this->stream->getServerLink()) == true) {
            $this->message = "Server loaded";
            $this->server = $server;
            return true;
        }
        $this->message = "Unable to load server";
        return false;
    }
    protected function loadAvatar(): bool
    {
        $avatar = new Avatar();
        if ($avatar->loadID($this->rental->getAvatarLink()) == true) {
            $this->message = "Avatar loaded";
            $this->avatar = $avatar;
            return true;
        }
        $this->message = "Unable to load avatar";
        return false;
    }
}
