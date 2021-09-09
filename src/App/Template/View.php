<?php

namespace App\Template;

use App\R7\Model\Slconfig;

class View extends TableView
{
    public function getSlConfigObject(): Slconfig
    {
        return $this->slconfig;
    }
    protected function ok(string $message): void
    {
        $this->setMessage($message, true);
    }
    protected function failed(string $message): void
    {
        $this->setMessage($message, false);
    }
    protected function setMessage(string $message, bool $status): void
    {
        $this->setSwapTag("status", $status);
        $this->setSwapTag("message", $message);
    }
}
