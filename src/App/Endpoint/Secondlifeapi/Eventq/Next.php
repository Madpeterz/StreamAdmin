<?php

namespace App\Endpoint\Secondlifeapi\Eventq;

use App\Models\Sets\EventsqSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("hasmessage", false);
        if ($this->owner_override == false) {
            $this->failed("SystemAPI access only - please contact support");
            return;
        }
        $eventqset = new EventsqSet();
        $eventqset->loadNewest(limit:1, orderDirection:"ASC");
        if ($eventqset->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $eventq = $eventqset->getFirst();
        $this->setSwapTag("hasmessage", true);
        $this->setSwapTag("eventName", $eventq->getEventName());
        $this->setSwapTag("eventMessage", $eventq->getEventMessage());
        if ($eventq->removeEntry()->status == false) {
            $this->setSwapTag("hasmessage", false);
            $this->failed("Unable to remove event from the q");
            return;
        }
        $this->ok("ok");
    }
}
