<?php

namespace App\Endpoint\View\Server;

use App\R7\Set\ApisSet;
use App\R7\Model\Server;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove server " . $this->page . "' 
        data-actiontext='Remove server' 
        data-actionmessage='This will fail if there is anything using this server!' 
        data-targetendpoint='[[url_base]]Server/Remove/" . $this->page . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");


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
        $form->group("Basic config");
        $form->col(6);
        $form->textInput("domain", "Domain", 30, $server->getDomain(), "ip or uncloudflared proxyed domain/subdomain");
        $form->textInput(
            "controlPanelURL",
            "Control panel",
            200,
            $server->getControlPanelURL(),
            "URL to the control panel"
        );
        $form->col(6);
        $form->select("apiLink", "API / type", $server->getApiLink(), $apis->getLinkedArray("id", "name"));
        $form->textInput("apiURL", "API / URL", 200, $server->getApiURL(), "");
        $form->textInput("apiUsername", "API / Username", 200, $server->getApiUsername(), "the API username");
        $form->textInput("apiPassword", "API / Password", 200, "NoChange", "the API password");
        $form->select(
            "apiServerStatus",
            "Panel / Server status",
            $server->getApiServerStatus(),
            $this->allowDisallow
        );
        $form->select(
            "apiSyncAccounts",
            "Panel / Sync accounts",
            $server->getApiSyncAccounts(),
            $this->allowDisallow
        );
        $form->split();
        $form->group("API Flags");
        $form->col(6);
        $form->select("optPasswordReset", "Opt / PWD reset", $server->getOptPasswordReset(), $this->allowDisallow);
        $form->select("optAutodjNext", "Opt / ADJ next", $server->getOptAutodjNext(), $this->allowDisallow);
        $form->select("optToggleAutodj", "Opt / ADJ toggle", $server->getOptToggleAutodj(), $this->allowDisallow);
        $form->select(
            "optToggleStatus",
            "Opt / Toggle status",
            $server->getOptToggleStatus(),
            $this->allowDisallow
        );
        $form->col(6);
        $form->select(
            "eventEnableStart",
            "Event / Enable on rental start",
            $server->getEventEnableStart(),
            $this->yesNo
        );
        $form->select(
            "eventStartSyncUsername",
            "Event / Customize username on rental start",
            $server->getEventStartSyncUsername(),
            $this->yesNo
        );
        $form->select(
            "eventEnableRenew",
            "Event / Enable on renewal",
            $server->getEventEnableRenew(),
            $this->yesNo
        );
        $form->select(
            "eventDisableExpire",
            "Event / Disable on expire",
            $server->getEventDisableExpire(),
            $this->yesNo
        );
        $form->split();
        $form->col(6);
        $form->select(
            "eventDisableRevoke",
            "Event / Disable on revoke",
            $server->getEventDisableRevoke(),
            $this->yesNo
        );
        $form->select(
            "eventResetPasswordRevoke",
            "Event / Reset password on revoke",
            $server->getEventResetPasswordRevoke(),
            $this->yesNo
        );
        $form->select(
            "eventRevokeResetUsername",
            "Event / Reset username on revoke",
            $server->getEventRevokeResetUsername(),
            $this->yesNo
        );
        $form->select(
            "eventClearDjs",
            "Event / Clear DJ accounts on revoke",
            $server->getEventClearDjs(),
            $this->yesNo
        );
        $form->col(6);
        $form->select(
            "eventRecreateRevoke",
            "Event / Recreate account on revoke",
            $server->getEventRecreateRevoke(),
            $this->yesNo
        );
        $form->select(
            "eventCreateStream",
            "Event / Create stream on server",
            $server->getEventCreateStream(),
            $this->yesNo
        );
        $form->select(
            "eventUpdateStream",
            "Event / Update stream on server",
            $server->getEventUpdateStream(),
            $this->yesNo
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
        include "" . ROOTFOLDER . "/App/Endpoint/View/Server/api_notes.php";
        include "" . ROOTFOLDER . "/App/Endpoint/View/Server/js_on_select_api.php";
    }
}
