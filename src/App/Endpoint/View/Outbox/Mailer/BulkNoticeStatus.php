<?php

namespace App\Endpoint\View\Outbox\Mailer;

use App\Models\Set\NoticeSet;
use YAPF\Bootstrap\Template\Form;

class BulkNoticeStatus
{
    /**
     * Create the form needed to bulk mail clients
     * @return string[] An array with the form title and the form itself.
     */
    public function getForm(): array
    {
        $notice_set = new NoticeSet();
        $notice_set->loadAll();

        $form = new Form();
        $form->target("outbox/bulk/notice");
        $form->mode("get");
        $form->col(4);
            $form->select("noticeLink", "Notice level", 0, $notice_set->getLinkedArray("id", "name"));
        $form->col(8);
            $form->textarea("messageStatus", "Message", 800, "", "Use swap tags as the placeholders! max length 800");
        return ["Send => Bulk [Notice]" => $form->render("Select avatars", "primary") .
        "<br/>Send mail to everyone with the selected notice level"];
    }
}
