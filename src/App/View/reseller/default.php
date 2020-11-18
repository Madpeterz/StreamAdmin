<?php

$this->output->addSwapTagString("page_title", " All");
$reseller_set = new reseller_set();
$reseller_set->loadAll();

$avatar_set = new avatar_set();
$avatar_set->loadIds($reseller_set->getAllByField("avatarlink"));

$table_head = ["id","Name","Allow","Rate"];
$table_body = [];

foreach ($reseller_set->getAllIds() as $reseller_id) {
    $reseller = $reseller_set->getObjectByID($reseller_id);
    $avatar = $avatar_set->getObjectByID($reseller->getAvatarlink());
    $entry = [];
    $entry[] = $reseller->getId();
    $entry[] = '<a href="[[url_base]]reseller/manage/' . $reseller->getId() . '">' . $avatar->getAvatarname() . '</a>';
    $entry[] = [false => "No",true => "Yes"][$reseller->get_allowed()];
    $entry[] = $reseller->get_rate();
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
$this->output->addSwapTagString("page_content", "<br/><hr/><p>
To register a new reseller please have them rez and activate any StreamAdmin object<br/>
if enabled in config they will be automagicly accepted<br/>
failing that added to this list for a member of staff to set the rate and enable.<br/>
<hr/>
Note: Even if the system assigned avatar appears in this list,
the settings defined for the reseller are ignored.
</p>");
