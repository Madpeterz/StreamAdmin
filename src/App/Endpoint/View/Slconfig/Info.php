<?php

namespace App\Endpoint\View\Slconfig;

class Info extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", " php info");
        $this->setSwapTag("page_title", " php info");
        $this->setSwapTag("page_actions", "");
        $this->setSwapTag("page_content", "");
        phpinfo();
    }
}
