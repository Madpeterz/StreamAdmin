<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\NoticeSet;

class Soon extends Withstatus
{
    public function process(): void
    {
        $noticeSet = new NoticeSet();
        $whereConfig = [
            "fields" => ["hoursRemaining","hoursRemaining"],
            "values" => [24,0],
            "types" => ["i","i"],
            "matches" => ["<=",">"],
        ];
        $noticeSet->loadWithConfig($whereConfig);

        $this->whereconfig = [
            "fields" => ["noticeLink"],
            "values" => [$noticeSet->getAllIds()],
            "types" => ["i"],
            "matches" => ["IN"],
        ];
        $this->output->addSwapTagString("page_title", "With notice status: Soon");
        parent::process();
    }
}
