<?php

namespace App\Endpoint\View\Transactions;

class FromAvatar extends Forms
{
    public function process(): void
    {

        $avd = $this->input->get("avatarsearch")->asString();
        if (strlen($avd) < 3) {
            $this->output->redirectWithMessage("transactions", "Please enter 3 letters/numbers at min");
            return;
        }
        $whereconfig = [
            "fields" => ["avatarUUID","avatarName","avatarUid"],
            "values" => [$avd,$avd,$avd],
            "types" => ["s","s","s"],
            "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
            "join_with" => ["OR","OR"],
        ];
        $this->avatar_set->loadWithConfig($whereconfig);
        $this->output->addSwapTagString("page_title", " Matching avatar seach: " . $avd);
        if ($this->avatar_set->getCount() > 1) {
            $this->output->addSwapTagString("page_title", " [Multiple entrys detected using first]");
        }
        $this->loadTransactionsFromAvatar($this->avatar_set->getFirst());
        parent::process();
    }
}
