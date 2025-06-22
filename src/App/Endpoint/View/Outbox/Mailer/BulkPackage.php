<?php

namespace App\Endpoint\View\Outbox\Mailer;

use App\Models\Set\PackageSet;
use YAPF\Bootstrap\Template\Form;

class BulkPackage
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
        $form->target("outbox/bulk/package");
        $form->mode("get");
        $form->col(4);
            $form->select("packageLink", "Package", 0, $package_set->getLinkedArray("id", "name"));
        $form->col(8);
            $form->textarea("messagePackage", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
        return ["Send => Bulk [Package]" => $form->render("Select avatars", "primary") .
        "<br/>Send mail to everyone with the selected package."];
    }
}
