<?php

namespace App\Endpoint\View\Auditlog;

use App\Models\Set\AuditlogSet;

class DefaultView extends View
{
    protected function emptyifnull(?string $input): string
    {
        if ($input === null) {
            return "";
        } elseif ($input === "1") {
            return "Yes";
        } elseif ($input === "0") {
            return "No";
        }
        return $input;
    }
    protected function convertCreateRemove(?string $input): string
    {
        $input = $this->emptyifnull($input);
        if ($input == "+++") {
            return "Create";
        } elseif ($input == "---") {
            return "Remove";
        }
        return $input;
    }
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() != 1) {
            $this->output->redirect("?bubblemessage=sorry owner only&bubbletype=warning");
            return;
        }
        $table_head = ["id","Store","Source id","Value name","Time","Note 1", "Note 2","Changed by"];
        $table_body = [];
        $auditlog = new AuditlogSet();
        $auditlog->loadNewest(25);
        $avatarSet = $auditlog->relatedAvatar();

        foreach ($auditlog as $log) {
            $avatar = $avatarSet->getObjectByID($log->getAvatarLink());
            $entry = [];
            $entry[] = $this->emptyifnull($log->getId());
            $entry[] = $log->getStore();
            $entry[] = $log->getSourceid();
            $entry[] = $this->convertCreateRemove($log->getValuename());
            $entry[] = date('l jS \of F Y h:i:s A', $log->getUnixtime());
            $entry[] = $this->emptyifnull($log->getOldvalue());
            $entry[] = $this->emptyifnull($log->getNewvalue());
            $entry[] = $avatar->getAvatarName();
            $table_body[] = $entry;
        }
        $this->setSwapTag(
            "page_content",
            $this->renderDatatable($table_head, $table_body) .
            "<br/><sub>Note 1 is normaly the old value, note 2 the new</sub>"
        );
    }
}
