<?php

namespace App\Endpoint\View\Outbox;

use App\Models\AvatarSet;
use App\Models\DetailSet;
use App\Models\RentalSet;

class Details extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent details");
        $table_head = ["id","Rental UID","Avatar name"];
        $table_body = [];
        $detail_set = new DetailSet();
        $detail_set->loadAll();
        $rental_set = new RentalSet();
        $rental_set->loadIds($detail_set->getAllByField("rentalLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($rental_set->getAllByField("avatarLink"));
        foreach ($detail_set->getAllIds() as $detail_id) {
            $detail = $detail_set->getObjectByID($detail_id);
            $rental = $rental_set->getObjectByID($detail->getRentalLink());
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $table_body[] = [$detail->getId(),$rental->getRentalUid(),$avatar->getAvatarName()];
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
