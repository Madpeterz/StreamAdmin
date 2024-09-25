<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Sets\EventsqSet;

class Events extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent custom events Q");
        $table_head = ["id","Name","Datetime"];
        $table_body = [];
        $eventqset = new EventsqSet();
        $eventqset->limitFields(["eventName","eventUnixtime"]);
        $eventqset->loadAll();
        foreach ($eventqset as $eventq) {
            $table_body[] = [
                $eventq->getId(),
                $eventq->getEventName(),
                date('d/m/Y @ G:i:s', $eventq->getEventUnixtime()),
            ];
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
