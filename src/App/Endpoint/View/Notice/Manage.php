<?php

namespace App\Endpoint\View\Notice;

use App\R7\Model\Notice;
use App\R7\Model\Noticenotecard;
use App\R7\Set\NoticenotecardSet;
use App\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", "<a href='[[url_base]]notice/remove/" . $this->page . "'>"
        . "<button type='button' class='btn btn-danger'>Remove</button></a>");

        if (in_array($this->page, [6,10]) == true) {
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
        if ($notice->loadID($this->page) == false) {
            $this->output->redirect("notice?bubblemessage=unable to find notice&bubbletype=warning");
            return;
        }
        if ($notice->getHoursRemaining() >= 999) {
            $this->output->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
            return;
        }
        $current_notecard_notice = new Noticenotecard();
        $current_notecard_notice->loadID($notice->getNoticeNotecardLink());
        $this->output->addSwapTagString("page_title", ":" . $notice->getName());
        $form = new form();
        $form->target("notice/update/" . $this->page . "");
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
        $form->select("useBot", "Use bot to send IM", $notice->getUseBot(), [false => "No",true => "Yes"]);
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
            [false => "No",true => "Yes"]
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
        include ROOTFOLDER . "/App/Endpoint/View/Shared/swaps_table.php";
    }
}
