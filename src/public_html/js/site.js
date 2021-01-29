var ajax_busy = false;
$(document).ready(function () {
    $(".ajaxonpageload").each(function (i, obj) {
        setTimeout(dynamic_ajax_load, (300 + Math.floor(Math.random() * 400)), $(this));
    });
    $(".avatarfinderajax").submit(function (e) {
        e.preventDefault();
        if (ajax_busy == false) {
            ajax_busy = true;
            var form = $(this);
            var url = form.attr('action');
            var method = form.attr('method');
            $.ajax({
                type: method,
                url: url,
                data: form.serialize(),
                success: function (data) {
                    try {
                        jsondata = JSON.parse(data);
                        var redirectdelay = 1500;
                        if (jsondata.hasOwnProperty('status')) {
                            if (jsondata.hasOwnProperty('values')) {
                                $("#finderscore").html(jsondata.values.score + "%");
                                $("#finderuid").html(jsondata.values.matchuid);
                                $("#findername").html(jsondata.values.matchname);
                            }
                            else {
                                $("#finderscore").html("0%");
                                $("#finderuid").html("-");
                                $("#findername").html("-");
                            }
                            setTimeout(function () { ajax_busy = false }, 1000);
                        }
                        else {
                            alert_error("Reply from server is not vaild please reload the page and try again.");
                            setTimeout(function () { ajax_busy = false }, 1000);
                        }
                    }
                    catch (e) {
                        alert_warning("Unable to process reply, please reload the page and try again!");
                        setTimeout(function () { ajax_busy = false }, 1000);
                    }
                },
                error: function (data) {
                    alert_error(data);
                    setTimeout(function () { ajax_busy = false }, 1000);
                }
            });
        }
    });
    $(".ajax").submit(function (e) {
        e.preventDefault();
        if (ajax_busy == false) {
            ajax_busy = true;
            var form = $(this);
            var url = form.attr('action');
            var method = form.attr('method');
            var timeout = 3000;
            if ($(this).hasClass("slow") == true) {
                alert_info("Please wait this request can be slow");
                timeout = 25000;
            }
            $.ajax({
                type: method,
                url: url,
                data: form.serialize(),
                timeout: timeout,
                success: function (data) {
                    try {
                        jsondata = JSON.parse(data);
                        var redirectdelay = 1500;
                        if (jsondata.hasOwnProperty('status')) {
                            if (jsondata.hasOwnProperty('message')) {
                                if (jsondata.status == true) {
                                    if (jsondata.message != "") alert_success(jsondata.message);
                                }
                                else {
                                    redirectdelay = 3500;
                                    if (jsondata.message != "") alert_warning(jsondata.message);
                                }
                            }
                            if (jsondata.hasOwnProperty('redirect')) {
                                jsondata.redirect = jsondata.redirect.replace("here", "");
                                var urlgoto = url_base + jsondata.redirect;
                                setTimeout(function () { $(location).attr('href', urlgoto) }, redirectdelay);
                            }
                            else {
                                setTimeout(function () { ajax_busy = false }, 1000);
                            }
                        }
                        else {
                            alert_error("Reply from server is not vaild please reload the page and try again.");
                            setTimeout(function () { ajax_busy = false }, 1000);
                        }
                    }
                    catch (e) {
                        alert_warning("Unable to process reply, please reload the page and try again!");
                        setTimeout(function () { ajax_busy = false }, 1000);
                    }
                },
                error: function (data) {
                    alert_error(data);
                    setTimeout(function () { ajax_busy = false }, 1000);
                }
            });
        }
    });
});

$('#NotecardModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var rentaluid = button.data('rentaluid') // Extract info from data-* attributes
    var modal = $(this)
    modal.find('#ModalTitle').text('Loading');
    modal.find('#ModalText').val("Fetching notecard for rental " + rentaluid);
    $.ajax({
        type: "post",
        url: url_base + "client/getnotecard/" + rentaluid,
        success: function (data) {
            try {
                jsondata = JSON.parse(data);
                var redirectdelay = 1500;
                if (jsondata.hasOwnProperty('status')) {
                    if (jsondata.hasOwnProperty('message')) {
                        modal.find('#ModalTitle').text('Notecard');
                        modal.find('#ModalText').val(jsondata.message);
                    }
                }
                else {
                    alert_error("Reply from server is not vaild please reload the page and try again.");
                }
            }
            catch (e) {
                alert_warning("Unable to process reply, please reload the page and try again!");
            }
        },
        error: function (data) {
            alert_error(data);
        }
    });
})
function dynamic_ajax_load(jqueryobject) {
    if (typeof jqueryobject.data('loading') !== 'undefined') {
        jqueryobject.html(jqueryobject.data('loading'));
    }
    var loadtype = "get";
    if (typeof jqueryobject.data('loadmethod') !== 'undefined') {
        loadtype = jqueryobject.data('loadmethod');
    }

    if (jqueryobject.html() != "-") {
        if (typeof jqueryobject.data('repeatingrate') !== 'undefined') {
            setTimeout(dynamic_ajax_load, (jqueryobject.data("repeatingrate") + Math.floor(Math.random() * 400)), jqueryobject);
        }
        $.ajax({
            type: loadtype,
            url: jqueryobject.data("loadurl"),
            success: function (data) {
                try {
                    jsondata = JSON.parse(data);
                    var redirectdelay = 1500;
                    if (jsondata.hasOwnProperty('status')) {
                        if (jsondata.hasOwnProperty('message') == true) {
                            jqueryobject.html(jsondata.message);
                        }
                        else {
                            jqueryobject.html("");
                        }
                    }
                    else {
                        jqueryobject.html("bad reply");
                    }
                }
                catch (e) {
                    jqueryobject.html("reply error");
                }
            },
            error: function (data) {
                jqueryobject.html("ajax error");
            }
        });
    }
}
function alert_success(smsg) {
    alert(smsg, "success");
}
function alert_error(smsg) {
    alert(smsg, "error");
}
function alert_warning(smsg) {
    alert(smsg, "warning");
}
function alert_info(smsg) {
    alert(smsg, "primary");
}
function alert(smsg, alerttype) {
    $.notify({
        // options
        message: smsg
    }, {
        // settings
        type: alerttype
    });
}
