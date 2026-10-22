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
            $avatar = $avatarSet->getObjectByID($log->_AvatarLink);
            $entry = [];
            $entry[] = $this->emptyifnull($log->_Id);
            $entry[] = $log->_Store;
            $entry[] = $log->_Sourceid;
            $entry[] = $this->convertCreateRemove($log->_Valuename);
            $entry[] = date('l jS \of F Y h:i:s A', $log->_Unixtime);
            $entry[] = $this->emptyifnull($log->_Oldvalue);
            $entry[] = $this->emptyifnull($log->_Newvalue);
            $entry[] = $avatar->_AvatarName;
            $table_body[] = $entry;
        }
        $this->setSwapTag(
            "page_content",
            $this->renderDatatable($table_head, $table_body) .
            "<br/><sub>Note 1 is normaly the old value, note 2 the new</sub>"
        );
    }
}
