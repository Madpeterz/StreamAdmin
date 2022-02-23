<?php

namespace App\Endpoint\View\Notice;

use App\Models\Sets\NoticenotecardSet;
use YAPF\Bootstrap\Template\Form as Form;

class Create extends View
{
    public function process(): void
    {
        $where_config = [
            "fields" => ["missing"],
            "values" => [0],
            "types" => ["i"],
            "matches" => ["="],
        ];
        $notice_notecard_set = new NoticenotecardSet();
        $notice_notecard_set->loadWithConfig($where_config);
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " : New");
        $this->setSwapTag("page_actions", "");

        $form = new form();
        $form->target("notice/create");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput("name", "Name", 30, "", "Name");
        $form->textarea("imMessage", "Message", 800, "", "use the swaps as placeholders [max length 800]");
        $form->col(6);
        $form->group("Config");
        $form->select("sendObjectIM", "Send the Object IM", true, $this->yesNo);
        $form->select("useBot", "Use bot to send IM", false, $this->yesNo);
        $form->numberInput("hoursRemaining", "Hours remain [Trigger at]", 24, 3, "Max value 999");
        $form->col(12);
        $form->directAdd("<br/>");
        $form->col(6);
        $form->group("Dynamic notecard [Requires bot]");
        $form->select("sendNotecard", "Enable", false, $this->yesNo);
        $form->textarea("notecardDetail", "Notecard content", 2000, "", "use the swaps as placeholders");
        $form->col(6);
        $form->group("Static notecard");
        $form->select("noticeNotecardLink", " ", 1, $notice_notecard_set->getLinkedArray("id", "name"));
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
        include ROOTFOLDER . "/App/Endpoint/View/Shared/swaps_table.php";
    }
}
