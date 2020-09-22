<?php
$template_parts["html_js_onready"] .= "
$('select[name=\"apilink\"]').change(function() {
    update_api_flags($(this));
});
function sync_api_flags(json)
{
    var flags = [
'event_recreate_revoke',
'event_clear_djs',
'event_revoke_reset_username',
'event_reset_password_revoke',
'event_disable_revoke',
'event_disable_expire',
'event_enable_renew',
'event_start_sync_username',
'event_enable_start',
'opt_toggle_status',
'opt_toggle_autodj',
'opt_autodj_next',
'opt_password_reset',
'api_serverstatus',
'api_sync_accounts'
];
$.each(flags, function( index, value )
{
    if(json.hasOwnProperty(value))
    {
        var newval = 0;
        if(json[value] == true) newval = 1;
        $('select[name=\"'+value+'\"]').val(newval);
    }
});
alert_info(\"Sync'd API flags with API config\");
}
function update_api_flags(caller)
{
    ajax_busy = true;
    $.ajax({
           type: 'post',
           url: '[[url_base]]ajax.php/server/getapiconfig',
           data: {
               'apilink': caller.val(),
           },
           success: function(data)
           {
               setTimeout(function(){ ajax_busy=false }, 1000);
               try
               {
                   jsondata = JSON.parse(data);
                   if(jsondata.hasOwnProperty('status'))
                   {
                       sync_api_flags(jsondata);
                   }
               }
               catch(e)
               {
               }
           },
           error: function (data)
           {
               setTimeout(function(){ ajax_busy=false }, 1000);
           }
    });
}
";
?>
