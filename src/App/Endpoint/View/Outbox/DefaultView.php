<?php

namespace App\Endpoint\View\Outbox;

use App\Template\PagedInfo;

class DefaultView extends View
{
    public function process(): void
    {
        global $pages;
        $pages = [];
        $Status = new Status();
        $Status->process();
        $this->output = $Status->getOutputObject();
        include ROOTFOLDER . "/App/Endpoint/View/Outbox/Mailer/bulk.package.php";
        include ROOTFOLDER . "/App/Endpoint/View/Outbox/Mailer/bulk.server.php";
        include ROOTFOLDER . "/App/Endpoint/View/Outbox/Mailer/bulk.status.php";
        include ROOTFOLDER . "/App/Endpoint/View/Outbox/Mailer/bulk.clients.php";

        include ROOTFOLDER . "/App/Flags/swaps_table_paged.php";
        include ROOTFOLDER . "/App/Endpoint/View/Shared/swaps_table.php";

        $paged_info = new PagedInfo();
        $this->setSwapTag("page_content", $paged_info->render($pages));
    }
}
