<?php

namespace App\Endpoint\View\SlConfig;

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
