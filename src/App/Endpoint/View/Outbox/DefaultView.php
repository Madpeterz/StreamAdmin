<?php

namespace App\Endpoints\View\Outbox;

use paged_info;

class DefaultView extends View
{
    public function process(): void
    {
        $pages = [];
        include "../View/Outbox/Status.php";
        include "../View/Outbox/Mailer/bulk.package.php";
        include "../View/Outbox/Mailer/bulk.server.php";
        include "../View/Outbox/Mailer/bulk.status.php";

        include "../App/Flags/swaps_table_paged.php";
        include "../App/View/Shared/swaps_table.php";

        $paged_info = new paged_info();
        $this->setSwapTag("page_content", $paged_info->render($pages));
    }
}
