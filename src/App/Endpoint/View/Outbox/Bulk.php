<?php

namespace App\Endpoint\View\Outbox;

use App\Models\AvatarSet;
use App\Models\BanlistSet;
use App\Models\Notice;
use App\Models\Package;
use App\Models\RentalSet;
use App\Models\Server;
use App\Models\StreamSet;
use App\Template\Form;
use YAPF\InputFilter\InputFilter;

class Bulk extends View
{
    public function process(): void
    {
        $rental_set = new RentalSet();

        $input_filter = new InputFilter();
        $source_id = -1;
        $souce_named = "";
        $ok = false;
        $message = $input_filter->getFilter("message");

        if (strlen($message) < 10) {
            $this->output->redirect("outbox?message=Message length to short");
            return;
        }
        if (strlen($message) > 800) {
            $this->output->redirect("outbox?message=Message length to long");
            return;
        }
        if ($this->page == "notice") {
            $source_id = $input_filter->getFilter("noticeLink", "integer");
            if ($source_id != null) {
                $notice = new Notice();
                $notice->loadID($source_id);
                $souce_named = $notice->getName();
                $rental_set->loadOnField("noticeLink", $source_id);
                $ok = true;
            }
        } elseif ($this->page == "server") {
            $source_id = $input_filter->getFilter("serverLink", "integer");
            if ($source_id != null) {
                $server = new Server();
                $server->loadID($source_id);
                $souce_named = $server->getDomain();
                $stream_set = new StreamSet();
                $stream_set->loadOnField("serverLink", $source_id);
                $rental_set->loadIds($stream_set->getAllIds(), "streamLink");
                $ok = true;
            }
        } elseif ($this->page == "package") {
            $source_id = $input_filter->getFilter("packageLink", "integer");
            if ($source_id != null) {
                $package = new Package();
                $package->loadID($source_id);
                $souce_named = $package->getName();
                $rental_set->loadOnField("packageLink", $source_id);
                $ok = true;
            }
        }
        if ($ok == false) {
            $this->output->redirect("outbox?message=Filter option not supported");
            return;
        }

        $this->output->addSwapTagString("page_title", " Bulk sending to " . $this->page . ": " . $souce_named);
        $stream_set = new StreamSet();
        $stream_set->loadIds($rental_set->getAllByField("streamLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($rental_set->getUniqueArray("avatarLink"));
        $banlist_set = new BanlistSet();
        $banlist_set->loadIds($rental_set->getUniqueArray("avatarLink"), "avatarLink");

        $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
        if ($max_avatar_count == 0) {
            $this->output->redirect("outbox?message=No selectable avatars for the " . $this->page);
            return;
        }
        $form = new Form();
        $form->target("outbox/send");
        $form->hiddenInput("message", $message);
        $form->hiddenInput("max_avatars", $max_avatar_count);
        $form->hiddenInput("source", $this->page);
        $form->hiddenInput("source_id", $source_id);

        $table_head = ["X","Name"];
        $table_body = [];

        $banned_ids = $banlist_set->getAllByField("avatarLink");
        foreach ($avatar_set->getAllIds() as $avatar_id) {
            if (in_array($avatar_id, $banned_ids) == false) {
                $avatar = $avatar_set->getObjectByID($avatar_id);
                $entry = [];
                $entry[] = '<div class="checkbox"><input checked type="checkbox" id="avatarmail' . $avatar_id
                . '" name="avatarids[]" value="' . $avatar_id . '"></div>';
                $entry[] = '<div class="checkbox"><label for="avatarmail'
                . $avatar_id . '">' . $avatar->getAvatarName() . '</label></div>';
                $table_body[] = $entry;
            }
        }
        $form->col(12);
            $form->directAdd(render_table($table_head, $table_body));
        $this->setSwapTag("page_content", $form->render("Send to selected", "success"));
        $this->output->addSwapTagString("page_content", "<br/><hr/>Note: If an avatar has multiple streams that "
        . "match the selected filter source the first rental will be used.");
    }
}
