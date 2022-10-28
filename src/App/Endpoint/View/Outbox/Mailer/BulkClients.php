<?php

namespace App\Endpoint\View\Outbox\Mailer;

use App\Models\Sets\PackageSet;
use YAPF\Bootstrap\Template\Form;

class BulkClients
{
    /**
     * Create the form needed to bulk mail clients
     * @return string[] An array with the form title and the form itself.
     */
    public function getForm(): array
    {
        $package_set = new PackageSet();
        $package_set->loadAll();

        $form = new Form();
        $form->target("outbox/bulk/clients");
        $form->mode("get");
        $form->col(8);
            $form->textarea("messageClients", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
        return ["Send => Bulk [All Clients]" => $form->render("Select avatars", "primary") .
        "<br/>Send mail to everyone who has a rental."];
    }
}