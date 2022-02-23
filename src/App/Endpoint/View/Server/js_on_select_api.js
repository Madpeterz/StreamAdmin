$('select[name="apiLink"]').change(function () {
    update_api_flags($(this));
});
function sync_api_flags(json) {
    var flags = [
        'eventRecreateRevoke',
        'eventClearDjs',
        'eventRevokeResetUsername',
        'eventResetPasswordRevoke',
        'eventDisableRevoke',
        'eventDisableExpire',
        'eventEnableRenew',
        'eventStartSyncUsername',
        'eventEnableStart',
        'optToggleStatus',
        'optToggleAutodj',
        'optAutodjNext',
        'optPasswordReset',
        'apiServerStatus',
        'apiSyncAccounts',
        'eventCreateStream',
        'eventUpdateStream'
    ];
    $.each(flags, function (index, value) {
        if (json.hasOwnProperty(value)) {
            var newval = 0;
            if (json[value] === true) newval = 1;
            $('select[name=\"' + value + '\"]').val(newval);
        }
    });
    alert_info("Sync'd API flags with API config");
}
function update_api_flags(caller) {
    ajax_busy = true;
    $.ajax({
        type: 'post',
        url: '[[SITE_URL]]ajax.php/server/getapiconfig',
        data: {
            'apiLink': caller.val(),
        },
        success: function (data) {
            setTimeout(function () { ajax_busy = false }, 1000);
            try {
                jsondata = JSON.parse(data);
                if (jsondata.hasOwnProperty('status')) {
                    sync_api_flags(jsondata);
                }
            }
            catch (e) {
            }
        },
        error: function (data) {
            setTimeout(function () { ajax_busy = false }, 1000);
        }
    });
}
