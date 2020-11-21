<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]server/remove/" . $this->page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
$server = new server();
$apis = new apis_set();
$apis->loadAll();
if ($server->loadID($this->page) == true) {
    $this->output->addSwapTagString("page_title", " :" . $server->getDomain());
    $form = new form();
    $form->target("server/update/" . $this->page . "");
    $form->required(true);
    $form->col(6);
        $form->textInput("domain", "Domain", 30, $server->getDomain(), "ip or uncloudflared proxyed domain/subdomain");
        $form->textInput("controlpanel_url", "Control panel", 200, $server->get_controlpanel_url(), "URL to the control panel");
    $form->col(6);
        $form->select("apilink", "API / type", $server->get_apilink(), $apis->getLinkedArray("id", "name"));
        $form->textInput("api_url", "API / URL", 200, $server->get_api_url(), "");
        $form->textInput("api_username", "API / Username", 200, $server->get_api_username(), "the API username");
        $form->textInput("api_password", "API / Password", 200, "NoChange", "the API password");
        $form->select("api_serverstatus", "Panel / Server status", $server->get_api_serverstatus(), [0 => "Disabled",1 => "Allow"]);
        $form->select("api_sync_accounts", "Panel / Sync accounts", $server->get_api_sync_accounts(), [0 => "Disabled",1 => "Allow"]);
    $form->split();
    $form->group("API Flags");
    $form->col(6);
        $form->select("opt_password_reset", "Opt / PWD reset", $server->get_opt_password_reset(), [0 => "Disabled",1 => "Allow"]);
        $form->select("opt_autodj_next", "Opt / ADJ next", $server->get_opt_autodj_next(), [0 => "Disabled",1 => "Allow"]);
        $form->select("opt_toggle_autodj", "Opt / ADJ toggle", $server->get_opt_toggle_autodj(), [0 => "Disabled",1 => "Allow"]);
        $form->select("opt_toggle_status", "Opt / Toggle status", $server->get_opt_toggle_status(), [0 => "Disabled",1 => "Allow"]);
    $form->col(6);
        $form->select("event_enable_start", "Event / Enable on rental start", $server->get_event_enable_start(), [0 => "No",1 => "Yes"]);
        $form->select("event_start_sync_username", "Event / Customize username on rental start", $server->get_event_start_sync_username(), [0 => "No",1 => "Yes"]);
        $form->select("event_enable_renew", "Event / Enable on renewal", $server->get_event_enable_renew(), [0 => "No",1 => "Yes"]);
        $form->select("event_disable_expire", "Event / Disable on expire", $server->get_event_disable_expire(), [0 => "No",1 => "Yes"]);
    $form->split();
    $form->col(6);
        $form->select("event_disable_revoke", "Event / Disable on revoke", $server->get_event_disable_revoke(), [0 => "No",1 => "Yes"]);
        $form->select("event_reset_password_revoke", "Event / Reset password on revoke", $server->get_event_reset_password_revoke(), [0 => "No",1 => "Yes"]);
        $form->select("event_revoke_reset_username", "Event / Reset username on revoke", $server->get_event_revoke_reset_username(), [0 => "No",1 => "Yes"]);
        $form->select("event_clear_djs", "Event / Clear DJ accounts on revoke", $server->get_event_clear_djs(), [0 => "No",1 => "Yes"]);
    $form->col(6);
        $form->select("event_recreate_revoke", "Event / Recreate account on revoke", $server->get_event_recreate_revoke(), [0 => "No",1 => "Yes"]);
        $form->select("event_create_stream", "Event / Create stream on server", $server->get_event_create_stream(), [0 => "No",1 => "Yes"]);
        $form->select("event_update_stream", "Event / Update stream on server", $server->get_event_update_stream(), [0 => "No",1 => "Yes"]);
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    include "webpanel/view/server/api_notes.php";
    include "webpanel/view/server/js_on_select_api.php";
} else {
    $this->output->redirect("server?bubblemessage=unable to find server&bubbletype=warning");
}
