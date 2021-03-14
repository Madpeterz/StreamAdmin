<?php

namespace App\Helpers\ServerApi;

use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;

abstract class SetServerApiHelper extends LoadServerApiHelper
{
    public function __construct(Stream $stream = null, bool $auto_load = true)
    {
        $this->forceSetStream($stream, $auto_load);
    }


    public function forceSetStream(Stream $stream = null, bool $auto_load = false): void
    {
        $this->stream = $stream;
        if ($stream == null) {
            return;
        }
        $this->stream = $stream;
        if ($auto_load == false) {
            return;
        }
        if ($this->loadServer() == false) {
            return;
        }
        if ($this->loadApi() == false) {
            return;
        }
        if ($this->loadPackage() == false) {
            return;
        }
        $this->serverApi->updatePackage($this->package);
        if ($this->loadRental() == false) {
            return;
        }
        $this->loadAvatar();
    }
    public function forceSetServer(Server $server): bool
    {
        $this->server = $server;
        return $this->loadApi();
    }
    public function forceSetRental(Rental $rental): bool
    {
        $this->rental = $rental;
        return $this->loadAvatar();
    }
    public function forceSetPackage(Package $package): bool
    {
        $this->package = $package;
        return true;
    }
}
