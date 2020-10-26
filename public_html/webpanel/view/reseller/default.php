<?php
$view_reply->add_swap_tag_string("page_title"," All");
$reseller_set = new reseller_set();
$reseller_set->loadAll();

$avatar_set = new avatar_set();
$avatar_set->load_ids($reseller_set->get_all_by_field("avatarlink"));

$table_head = array("id","Name","Allow","Rate");
$table_body = array();

foreach($reseller_set->get_all_ids() as $reseller_id)
{
    $reseller = $reseller_set->get_object_by_id($reseller_id);
    $avatar = $avatar_set->get_object_by_id($reseller->get_avatarlink());
    $entry = array();
    $entry[] = $reseller->get_id();
    $entry[] = '<a href="[[url_base]]reseller/manage/'.$reseller->get_id().'">'.$avatar->get_avatarname().'</a>';
    $entry[] = array(false=>"No",true=>"Yes")[$reseller->get_allowed()];
    $entry[] = $reseller->get_rate();
    $table_body[] = $entry;
}
$view_reply->set_swap_tag_string("page_content",render_datatable($table_head,$table_body));
$view_reply->add_swap_tag_string("page_content","<br/><hr/><p>
To register a new reseller please have them rez and activate any StreamAdmin object<br/>
if enabled in config they will be automagicly accepted<br/>
failing that added to this list for a member of staff to set the rate and enable.<br/>
<hr/>
Note: Even if the system assigned avatar appears in this list,
the settings defined for the reseller are ignored.
</p>");
?>
