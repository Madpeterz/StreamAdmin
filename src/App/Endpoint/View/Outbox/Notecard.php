<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Sets\NotecardSet;

class Notecard extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent notecards");
        $table_head = ["id","Rental UID","Avatar name"];
        $table_body = [];
        $notecard_set = new NotecardSet();
        $notecard_set->loadAll();
        $rental_set = $notecard_set->relatedRental();
        $avatar_set = $rental_set->relatedAvatar();
        foreach ($notecard_set as $notecard) {
            $rental = $rental_set->getObjectByID($notecard->getRentalLink());
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $entry = [];
            $entry[] = $notecard->getId();
            $entry[] = $rental->getRentalUid();
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
