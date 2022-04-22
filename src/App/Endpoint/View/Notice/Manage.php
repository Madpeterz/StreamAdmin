<?php

namespace App\Endpoint\View\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Models\Sets\NoticenotecardSet;
use YAPF\Bootstrap\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", "<a href='[[SITE_URL]]notice/remove/" . $this->siteConfig->getPage() . "'>"
        . "<button type='button' class='btn btn-danger'>Remove</button></a>");

        if (in_array($this->siteConfig->getPage(), [6,10]) == true) {
            $this->setSwapTag("page_actions", "");
        }
        $where_config = [
            "fields" => ["missing"],
            "values" => [0],
            "types" => ["i"],
            "matches" => ["="],
        ];

        $notice_notecard_set = new NoticenotecardSet();
        $notice_notecard_set->loadWithConfig($where_config);

        $notice = new Notice();
        if ($notice->loadID($this->siteConfig->getPage())->status == false) {
            $this->output->redirect("notice?bubblemessage=unable to find notice&bubbletype=warning");
            return;
        }
        if ($notice->getHoursRemaining() >= 999) {
            $this->output->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
            return;
        }
        $current_notecard_notice = new Noticenotecard();
        $current_notecard_notice->loadID($notice->getNoticeNotecardLink());
        $this->output->addSwapTagString("page_title", " : " . $notice->getName());
        $form = new form();
        $form->target("notice/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput("name", "Name", 30, $notice->getName(), "Name");
        $form->textarea(
            "imMessage",
            "Message",
            800,
            $notice->getImMessage(),
            "use the swaps as placeholders [max length 800]"
        );
        $form->col(6);
        $form->group("Config");
        $form->select("sendObjectIM", "Send the Object IM", $notice->getSendObjectIM(), $this->yesNo);
        $form->select("useBot", "Use bot to send IM", $notice->getUseBot(), $this->yesNo);
        $form->numberInput(
            "hoursRemaining",
            "Hours remain [Trigger at]",
            $notice->getHoursRemaining(),
            3,
            "Max value 998"
        );
        $form->col(12);
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("Dynamic notecard [Requires bot]");
        $form->select(
            "sendNotecard",
            "Enable",
            $notice->getSendNotecard(),
            $this->yesNo
        );
        $form->textarea(
            "notecardDetail",
            "Notecard content",
            2000,
            $notice->getNotecardDetail(),
            "use the swaps as placeholders"
        );
        $form->col(6);
        $form->group("Static notecard");
        $use_notecard_link = $notice->getNoticeNotecardLink();
        if (in_array($use_notecard_link, $notice_notecard_set->getAllIds()) == false) {
            $use_notecard_link = 1;
            $form->directAdd("<div class=\"alert alert-danger\" role=\"alert\">"
            . "Current notecard \"" . $current_notecard_notice->getName() . "\" is missing</div>");
        }
        $form->select(
            "noticeNotecardLink",
            " ",
            $use_notecard_link,
            $notice_notecard_set->getLinkedArray("id", "name")
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
        parent::getSwaps();
    }
}
