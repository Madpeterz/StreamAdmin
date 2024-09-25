<?php

namespace App\Endpoint\View\Outbox\Mailer;

use App\Models\Sets\ServerSet;
use YAPF\Bootstrap\Template\Form;

class BulkServer
{
    /**
     * Create the form needed to bulk mail clients
     * @return string[] An array with the form title and the form itself.
     */
    public function getForm(): array
    {
        $server_set = new ServerSet();
        $server_set->loadAll();

        $form = new Form();
        $form->target("outbox/bulk/server");
        $form->mode("get");
        $form->col(4);
            $form->select("serverLink", "Server", 0, $server_set->getLinkedArray("id", "domain"));
        $form->col(8);
            $form->textarea("messageServer", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
        return ["Send => Bulk [Server]" => $form->render("Select avatars", "primary") .
        "<br/>Send mail to everyone with the selected server."];
    }
}
