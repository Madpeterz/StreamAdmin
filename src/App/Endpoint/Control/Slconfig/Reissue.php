<?php

namespace App\Endpoint\Control\SlConfig;

use App\Template\ControlAjax;

class ReIssue extends ControlAjax
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
        $this->siteConfig->getSlConfig()->setSlLinkCode($this->lazyPW(8));
        $this->siteConfig->getSlConfig()->setHttpInboundSecret($this->lazyPW(8));
        $this->siteConfig->getSlConfig()->setPublicLinkCode($this->lazyPW(8));
        $this->siteConfig->getSlConfig()->setHudLinkCode($this->lazyPW(8));
    }
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Only system owner can adjust settings");
            return;
        }
        $this->reissueKeys();
        $update_status = $this->siteConfig->getSlConfig()->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update system config: %1\$s", $update_status->message)
            );
            return;
        }
        $this->ok("keys reissued!");
        $this->setSwapTag("redirect", "slconfig");
    }
}
