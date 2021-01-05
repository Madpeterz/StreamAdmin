<?php

namespace App\Endpoints\View\Notice;

use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Models\NoticenotecardSet;
use App\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]notice/remove/" . $this->page . "'>"
        . "<button type='button' class='btn btn-danger'>Remove</button></a>");
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
        if ($notice->getHoursremaining() >= 999) {
            $this->output->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
            return;
        }
        $current_notecard_notice = new Noticenotecard();
        $current_notecard_notice->loadID($notice->getNotice_notecardlink());
        $this->output->addSwapTagString("page_title", ":" . $notice->getName());
        $form = new form();
        $form->target("notice/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput("name", "Name", 30, $notice->getName(), "Name");
        $form->textarea(
            "immessage",
            "Message",
            800,
            $notice->getImmessage(),
            "use the swaps as placeholders [max length 800]"
        );
        $form->col(6);
        $form->group("Config");
        $form->select("usebot", "Use bot to send IM", $notice->getUsebot(), [false => "No",true => "Yes"]);
        $form->numberInput(
            "hoursremaining",
            "Hours remain [Trigger at]",
            $notice->getHoursremaining(),
            3,
            "Max value 998"
        );
        $form->col(12);
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("Dynamic notecard [Requires bot]");
        $form->select(
            "send_notecard",
            "Enable",
            $notice->getSend_notecard(),
            [false => "No",true => "Yes"]
        );
        $form->textarea(
            "notecarddetail",
            "Notecard content",
            2000,
            $notice->getNotecarddetail(),
            "use the swaps as placeholders"
        );
        $form->col(6);
        $form->group("Static notecard");
        $use_notecard_link = $notice->getNotice_notecardlink();
        if (in_array($use_notecard_link, $notice_notecard_set->getAllIds()) == false) {
            $use_notecard_link = 1;
            $form->directAdd("<div class=\"alert alert-danger\" role=\"alert\">"
            . "Current notecard \"" . $current_notecard_notice->getName() . "\" is missing</div>");
        }
        $form->select(
            "notice_notecardlink",
            " ",
            $use_notecard_link,
            $notice_notecard_set->getLinkedArray("id", "name")
        );
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
        include "webpanel/view/shared/swaps_table.php";
    }
}
