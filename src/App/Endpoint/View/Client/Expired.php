<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\NoticeSet;

class Expired extends Withstatus
{
    public function process(): void
    {
        $noticeSet = new NoticeSet();
        $whereConfig = [
            "fields" => ["hoursRemaining"],
            "values" => [0],
            "types" => ["i"],
            "matches" => ["<="],
        ];
        $noticeSet->loadWithConfig($whereConfig);

        $this->whereconfig = [
            "fields" => ["noticeLink"],
            "values" => [$noticeSet->getAllIds()],
            "types" => ["i"],
            "matches" => ["IN"],
        ];
        $this->output->addSwapTagString("page_title", "With notice status: Expired (or worse)");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]client/Bulkremove'>
            <button type='button' class='btn btn-danger'>Bulk remove</button></a>"
        );
        parent::process();
    }
}
