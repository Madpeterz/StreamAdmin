<?php

namespace App\Helpers;

use App\R7\Model\Reseller;

class ResellerHelper
{
    protected ?Reseller $reseller = null;
    public function getReseller(): ?Reseller
    {
        return $this->reseller;
    }
    public function loadOrCreate(
        int $avatarLinkid,
        bool $auto_accept = false,
        int $auto_accept_rate = 0
    ): bool {
        $this->reseller = new Reseller();
        if ($avatarLinkid < 1) {
            return false;
        }
        if ($this->reseller->loadByField("avatarLink", $avatarLinkid) == false) {
            $this->reseller = new Reseller();
            $this->reseller->setAvatarLink($avatarLinkid);
            $this->reseller->setAllowed($auto_accept);
            $this->reseller->setRate($auto_accept_rate);
            $save_status = $this->reseller->createEntry();
            return $save_status["status"];
        }
        return true;
    }
}
