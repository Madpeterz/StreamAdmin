<?php

namespace App\Endpoint\View\Notice;

use App\Models\Sets\NoticeSet;
use YAPF\Bootstrap\Template\Form as Form;

class Remove extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", " Remove notice:" . $this->siteConfig->getPage());
        $this->setSwapTag("page_actions", "");
        if (in_array($this->siteConfig->getPage(), [6,10]) == true) {
            $this->output->redirectWithMessage("notice", "This notice is protected", "danger");
            return;
        }

        $noticeLevels = new NoticeSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [$this->siteConfig->getPage()],
            "matches" => ["!="],
        ];
        $noticeLevels->loadWithConfig($whereConfig);

        if ($noticeLevels->getCount() == 0) {
            $this->output->redirectWithMessage("notice", "No notice levels loaded", "warning");
            return;
        }
        $bits = $noticeLevels->getLinkedArray("id", "name");
        $form = new form();
        $form->target("notice/remove/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(12);
        $form->group("</h4><p>Please assign a new notice level to transfer any Clients to.<br/>
        Please note: This action will fail if the Notice is in the Q to be used for a notecard!</p><h4>");
        $form->col(4);
        $form->select("newNoticeLevel", "Notice level", $this->siteConfig->getPage(), $bits);

        $action = '<br/>
<div class="btn-group btn-group-toggle" data-toggle="buttons">
<label class="btn btn-outline-danger active">
<input type="radio" value="Accept" name="accept" autocomplete="off" > Accept
</label>
<label class="btn btn-outline-secondary">
<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked> Nevermind
</label>
</div>';
        $form->directAdd($action);
        $this->setSwapTag("page_content", $form->render("Remove & Transfer", "danger"));
    }
}
