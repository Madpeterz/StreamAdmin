<?php

namespace App\Endpoint\View\Outbox;

use App\Endpoint\View\Outbox\Mailer\BulkClients;
use App\Endpoint\View\Outbox\Mailer\BulkNoticeStatus;
use App\Endpoint\View\Outbox\Mailer\BulkPackage;
use App\Endpoint\View\Outbox\Mailer\BulkServer;
use YAPF\Bootstrap\Template\PagedInfo;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Info");
        $Status = new Status();
        $Status->process();
        $this->pages = array_merge(
            $Status->getPages(),
            (new BulkClients())->getForm(),
            (new BulkNoticeStatus())->getForm(),
            (new BulkServer())->getForm(),
            (new BulkPackage())->getForm()
        );
        $this->use_paged_swaps = true;
        $this->getSwaps();
        $paged_info = new PagedInfo();
        $this->setSwapTag("page_content", $paged_info->render($this->pages));
    }
}
