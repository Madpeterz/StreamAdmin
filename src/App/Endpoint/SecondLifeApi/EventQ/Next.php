<?php

namespace App\Endpoint\SecondLifeApi\EventQ;

use App\R7\Set\EventsqSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("hasmessage", 0);
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        $eventqset = new EventsqSet();
        $eventqset->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($eventqset->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }
        $eventq = $eventqset->getFirst();

        $remove_status = $eventq->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove event from the q");
            return;
        }
        $this->setSwapTag("hasmessage", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("eventName", $eventq->getEventName());
        $this->setSwapTag("eventMessage", $eventq->getEventMessage());
        $this->setSwapTag("status", true);
    }
}
