<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\DetailSet;
use App\Models\Set\RentalSet;
use YAPF\Bootstrap\Template\Form;

class Bulkremove extends RenderList
{
    protected DetailSet $detailsRequestsSet;
    protected function loader(): void
    {
        $whereconfig = [
            "fields" => ["expireUnixtime"],
            "values" => [time()],
            "types" => ["i"],
            "matches" => ["<="],
        ];
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadWithConfig($whereconfig);
        $this->avatarSet = $this->rentalSet->relatedAvatar();
        $this->streamSet = $this->rentalSet->relatedStream();
        $this->serverSet = $this->streamSet->relatedServer();
        $this->detailsRequestsSet = $this->rentalSet->relatedDetail();
    }
    public function process(): void
    {
        $this->setSwapTag("html_title", "Clients");
        $this->output->addSwapTagString("page_title", "Bulk remove");
        $this->setSwapTag("page_actions", "");

        $table_head = ["id", "Action", "Avatar", "Server", "Port", "Expired", "Message"];
        $table_body = [];

        $this->loader();

        foreach ($this->rentalSet as $rental) {
            $avatar = $this->avatarSet->getObjectByID($rental->_AvatarLink);
            $stream = $this->streamSet->getObjectByID($rental->_StreamLink);
            $server = $this->serverSet->getObjectByID($stream->_ServerLink);
            $detail = $this->detailsRequestsSet->getObjectByField("rentalLink", $rental->_Id);
            if ($detail != null) {
                continue;
            }
            if ($rental->_NoticeLink != 6) {
                continue;
            }

            $entry = [];
            $entry[] = $rental->_Id;
            $entry[] = $this->makeButton($rental->_RentalUid);
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->_AvatarName . '">'
                . $avatar->_AvatarName . '</a>';
            $entry[] = $server->_Domain;
            $entry[] = $stream->_Port;
            $entry[] = $this->expiredAgo($rental->_ExpireUnixtime);
            $entry[] = '<textarea readonly class="form-control" id="message' . $rental->_Id . '" 
            name="message" placeholder="" cols="35" rows="2">' . $rental->_Message . '</textarea>';
            $table_body[] = $entry;
        }

        $this->setSwapTag("page_content", "No clients to remove right now");
        if (count($table_body) > 0) {
            $form = new Form();
            $form->target("client/Bulkremove");
            $form->col(12);
            $form->directAdd($this->renderDatatable($table_head, $table_body));
            $this->setSwapTag("page_content", $form->render("Process", "outline-danger"));
            $this->output->addSwapTagString(
                "page_content",
                "<br/><p>Clients with pending actions [see outbox] will not be listed!<br/>
                but might appear in the skipped counter!.</p>"
            );
        }
    }


    protected function makeButton(
        string $name = ""
    ): string {
        return '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
  <label class="btn btn-outline-danger active">
    <input type="radio" value="purge" name="rental' . $name . '" autocomplete="off"> Remove
  </label>
  <label class="btn btn-outline-secondary">
    <input type="radio" value="keep" name="rental' . $name . '" autocomplete="off" checked> Skip
  </label>
</div>';
    }
}
