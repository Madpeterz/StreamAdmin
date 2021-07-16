<?php

namespace App\Endpoint\Control\Slconfig;

use App\R7\Model\Avatar;
use App\R7\Model\Timezones;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Reissue extends ViewAjax
{
    protected function lazyPW(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public function reissueKeys(): void
    {
        $this->slconfig->setSlLinkCode($this->lazyPW(8));
        $this->slconfig->setHttpInboundSecret($this->lazyPW(8));
        $this->slconfig->setPublicLinkCode($this->lazyPW(8));
        $this->slconfig->setHudLinkCode($this->lazyPW(8));
    }
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->setSwapTag("status", false);
            $this->setSwapTag("message", "Only system owner can adjust settings");
            return;
        }
        $this->reissueKeys();
        $update_status = $this->slconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update system config: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "keys reissued!");
        $this->setSwapTag("redirect", "slconfig");
    }
}
