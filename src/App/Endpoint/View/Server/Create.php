<?php

namespace App\Endpoint\View\Server;

use App\Models\ApisSet;
use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " : New");
        $this->setSwapTag("page_actions", "");
        $apis = new ApisSet();
        $apis->loadAll();

        $form = new form();
        $form->target("server/create");
        $form->required(true);
        $form->group("Basic config");
        $form->col(6);
            $form->textInput("domain", "Domain", 30, "", "ip or uncloudflared proxyed domain/subdomain");
            $form->textInput("controlPanelURL", "Control panel", 200, "", "URL to the control panel");
        $form->col(6);
            $form->select("apiLink", "API / type", 0, $apis->getLinkedArray("id", "name"));
            $form->textInput("apiURL", "API / URL", 200, "", "the full url to the api endpoint");
            $form->textInput("apiUsername", "API / Username", 200, "", "the API username");
            $form->textInput("apiPassword", "API / Password", 200, "", "the API password");
            $form->select("apiServerStatus", "Panel / Server status", 1, $this->allowDisallow);
            $form->select("apiSyncAccounts", "Panel / Sync accounts", 1, $this->allowDisallow);
        $form->split();
        $form->group("API Flags");
        $form->col(6);
            $form->select("optPasswordReset", "Opt / PWD reset", 1, $this->allowDisallow);
            $form->select("optAutodjNext", "Opt / ADJ next", 1, $this->allowDisallow);
            $form->select("optToggleAutodj", "Opt / ADJ toggle", 1, $this->allowDisallow);
            $form->select("optToggleStatus", "Opt / Toggle status", 1, $this->allowDisallow);
        $form->col(6);
            $form->select("eventEnableStart", "Event / Enable on rental start", 1, $this->yesNo);
            $form->select(
                "eventStartSyncUsername",
                "Event / Customize username on rental start",
                0,
                $this->yesNo
            );
            $form->select("eventEnableRenew", "Event / Enable on renewal", 1, $this->yesNo);
            $form->select("eventDisableExpire", "Event / Disable on expire", 0, $this->yesNo);
        $form->col(6);
            $form->select("eventDisableRevoke", "Event / Disable on revoke", 1, $this->yesNo);
            $form->select("eventResetPasswordRevoke", "Event / Reset password on revoke", 1, $this->yesNo);
            $form->select("eventRevokeResetUsername", "Event / Reset username on revoke", 1, $this->yesNo);
            $form->select("eventClearDjs", "Event / Clear DJ accounts on revoke", 0, $this->yesNo);
        $form->col(6);
            $form->select("eventRecreateRevoke", "Event / Recreate account on revoke", 0, $this->yesNo);
            $form->select("eventCreateStream", "Event / Create stream on server", 0, $this->yesNo);
            $form->select("eventUpdateStream", "Event / Update stream on server", 0, $this->yesNo);
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
        include "../App/View/Server/api_notes.php";
        include "../App/View/Server/js_on_select_api.php";
    }
}
