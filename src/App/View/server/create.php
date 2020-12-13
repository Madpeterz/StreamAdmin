<?php

namespace App\View\Server;

use App\Models\ApisSet;
use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " : New");
        $this->output->setSwapTagString("page_actions", "");
        $apis = new ApisSet();
        $apis->loadAll();

        $form = new form();
        $form->target("server/create");
        $form->required(true);
        $form->group("Basic config");
        $form->col(6);
            $form->textInput("domain", "Domain", 30, "", "ip or uncloudflared proxyed domain/subdomain");
            $form->textInput("controlpanel_url", "Control panel", 200, "", "URL to the control panel");
        $form->col(6);
            $form->select("apilink", "API / type", 0, $apis->getLinkedArray("id", "name"));
            $form->textInput("api_url", "API / URL", 200, "", "the full url to the api endpoint");
            $form->textInput("api_username", "API / Username", 200, "", "the API username");
            $form->textInput("api_password", "API / Password", 200, "", "the API password");
            $form->select("api_serverstatus", "Panel / Server status", 1, $this->allowDisallow);
            $form->select("api_sync_accounts", "Panel / Sync accounts", 1, $this->allowDisallow);
        $form->split();
        $form->group("API Flags");
        $form->col(6);
            $form->select("opt_password_reset", "Opt / PWD reset", 1, $this->allowDisallow);
            $form->select("opt_autodj_next", "Opt / ADJ next", 1, $this->allowDisallow);
            $form->select("opt_toggle_autodj", "Opt / ADJ toggle", 1, $this->allowDisallow);
            $form->select("opt_toggle_status", "Opt / Toggle status", 1, $this->allowDisallow);
        $form->col(6);
            $form->select("event_enable_start", "Event / Enable on rental start", 1, $this->yesNo);
            $form->select(
                "event_start_sync_username",
                "Event / Customize username on rental start",
                0,
                $this->yesNo
            );
            $form->select("event_enable_renew", "Event / Enable on renewal", 1, $this->yesNo);
            $form->select("event_disable_expire", "Event / Disable on expire", 0, $this->yesNo);
        $form->col(6);
            $form->select("event_disable_revoke", "Event / Disable on revoke", 1, $this->yesNo);
            $form->select("event_reset_password_revoke", "Event / Reset password on revoke", 1, $this->yesNo);
            $form->select("event_revoke_reset_username", "Event / Reset username on revoke", 1, $this->yesNo);
            $form->select("event_clear_djs", "Event / Clear DJ accounts on revoke", 0, $this->yesNo);
        $form->col(6);
            $form->select("event_recreate_revoke", "Event / Recreate account on revoke", 0, $this->yesNo);
            $form->select("event_create_stream", "Event / Create stream on server", 0, $this->yesNo);
            $form->select("event_update_stream", "Event / Update stream on server", 0, $this->yesNo);
        $this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
        include "../App/View/Server/api_notes.php";
        include "../App/View/Server/js_on_select_api.php";
    }
}
