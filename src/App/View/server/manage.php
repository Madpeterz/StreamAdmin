<?php

namespace App\View\Server;

use App\Models\ApisSet;
use App\Models\Server;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]server/remove/"
            . $this->page . "'><button type='button' class='btn btn-danger'>Remove</button></a>"
        );
        $server = new Server();
        $apis = new ApisSet();
        $apis->loadAll();
        if ($server->loadID($this->page) == false) {
            $this->output->redirect("server?bubblemessage=unable to find server&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", " :" . $server->getDomain());
        $form = new Form();
        $form->target("server/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput("domain", "Domain", 30, $server->getDomain(), "ip or uncloudflared proxyed domain/subdomain");
        $form->textInput(
            "controlpanel_url",
            "Control panel",
            200,
            $server->getControlpanel_url(),
            "URL to the control panel"
        );
        $form->col(6);
        $form->select("apilink", "API / type", $server->getApilink(), $apis->getLinkedArray("id", "name"));
        $form->textInput("api_url", "API / URL", 200, $server->getApi_url(), "");
        $form->textInput("api_username", "API / Username", 200, $server->getApi_username(), "the API username");
        $form->textInput("api_password", "API / Password", 200, "NoChange", "the API password");
        $form->select(
            "api_serverstatus",
            "Panel / Server status",
            $server->getApi_serverstatus(),
            $this->allowDisallow
        );
        $form->select(
            "api_sync_accounts",
            "Panel / Sync accounts",
            $server->getApi_sync_accounts(),
            $this->allowDisallow
        );
        $form->split();
        $form->group("API Flags");
        $form->col(6);
        $form->select("opt_password_reset", "Opt / PWD reset", $server->getOpt_password_reset(), $this->allowDisallow);
        $form->select("opt_autodj_next", "Opt / ADJ next", $server->getOpt_autodj_next(), $this->allowDisallow);
        $form->select("opt_toggle_autodj", "Opt / ADJ toggle", $server->getOpt_toggle_autodj(), $this->allowDisallow);
        $form->select(
            "opt_toggle_status",
            "Opt / Toggle status",
            $server->getOpt_toggle_status(),
            $this->allowDisallow
        );
        $form->col(6);
        $form->select(
            "event_enable_start",
            "Event / Enable on rental start",
            $server->getEvent_enable_start(),
            $this->yesNo
        );
        $form->select(
            "event_start_sync_username",
            "Event / Customize username on rental start",
            $server->getEvent_start_sync_username(),
            $this->yesNo
        );
        $form->select(
            "event_enable_renew",
            "Event / Enable on renewal",
            $server->getEvent_enable_renew(),
            $this->yesNo
        );
        $form->select(
            "event_disable_expire",
            "Event / Disable on expire",
            $server->getEvent_disable_expire(),
            $this->yesNo
        );
        $form->split();
        $form->col(6);
        $form->select(
            "event_disable_revoke",
            "Event / Disable on revoke",
            $server->getEvent_disable_revoke(),
            $this->yesNo
        );
        $form->select(
            "event_reset_password_revoke",
            "Event / Reset password on revoke",
            $server->getEvent_reset_password_revoke(),
            $this->yesNo
        );
        $form->select(
            "event_revoke_reset_username",
            "Event / Reset username on revoke",
            $server->getEvent_revoke_reset_username(),
            $this->yesNo
        );
        $form->select(
            "event_clear_djs",
            "Event / Clear DJ accounts on revoke",
            $server->getEvent_clear_djs(),
            $this->yesNo
        );
        $form->col(6);
        $form->select(
            "event_recreate_revoke",
            "Event / Recreate account on revoke",
            $server->getEvent_recreate_revoke(),
            $this->yesNo
        );
        $form->select(
            "event_create_stream",
            "Event / Create stream on server",
            $server->getEvent_create_stream(),
            $this->yesNo
        );
        $form->select(
            "event_update_stream",
            "Event / Update stream on server",
            $server->getEvent_update_stream(),
            $this->yesNo
        );
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
        include "../App/View/Server/api_notes.php";
        include "../App/View/Server/js_on_select_api.php";
    }
}
