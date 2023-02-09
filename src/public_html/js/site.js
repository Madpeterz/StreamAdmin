var ajax_busy = false;
function getUrlParameter(variable)
{
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0;i<vars.length;i++) {
           var pair = vars[i].split("=");
           if(pair[0] == variable){return pair[1];}
   }
   return(false);
}

function attachInputFocusCounters()
{
    $('.inputwithlimit').change(function (e) { 
        var focusedID = $(this).attr('id');        
        updateTextCounterBox(focusedID);
    });
    $('.inputwithlimit').keyup(function (e) {
        var focusedID = $(this).attr('id');        
        updateTextCounterBox(focusedID);
    });
    $('.inputwithlimit').each(function (i, obj) {
        var focusedID = $(obj).attr('id');        
        var maxCount = $("#"+focusedID+"").data("lengthmax");
        var current = $("#"+focusedID+"").val().length;
        $("#"+focusedID+"current").html(current);
        $("#"+focusedID+"max").html(maxCount);
    });
}
function updateTextCounterBox(focusedID)
{
    var maxCount = $("#"+focusedID+"").data("lengthmax");
    var hasMin = $("#"+focusedID+"").data("lengthmin");
    if(hasMin == null) {
        hasMin = false;
    } else {
        hasMin = true;
    }

    var current = $("#"+focusedID+"").val().length;
    $("#"+focusedID+"current").html(current);
    $("#"+focusedID+"usedinput").removeClass("text-danger");
    $("#"+focusedID+"usedinput").addClass("text-muted");
    if(current > maxCount) {
        $("#"+focusedID+"usedinput").addClass("text-danger");
        $("#"+focusedID+"usedinput").removeClass("text-muted");
    }
    if(hasMin == true) {
        var min = $("#"+focusedID+"").data("lengthmin");
        if(current < min) {
            $("#"+focusedID+"usedinput").addClass("text-danger");
            $("#"+focusedID+"usedinput").removeClass("text-muted");
        }
    }
}
var lastCheckbox = false;
var startUnixtime = 0;
function startLoginTimer(setstartUnixtime)
{
    setInterval(loginTimerEvent, 1000);
    startUnixtime = setstartUnixtime;
}

function loginTimerEvent()
{
    var unixtimeNow = Math.floor(Date.now() / 1000);
    var dif = unixtimeNow - startUnixtime;
    var twoHoursNeg = ((60*60)*2) - dif;
    var minsRemaining = 0;
    while(twoHoursNeg > 60) {
        minsRemaining = minsRemaining + 1;
        twoHoursNeg = twoHoursNeg - 60;
    }
    $("#logintimeleft").html(minsRemaining);
    if(minsRemaining <= 0) {
        location.reload();
    }
}

$(document).ready(function () {  

    var tab = getUrlParameter("tab");
    if(tab != false) {
        $("#"+tab).click();
    }
    var bubblemessage = getUrlParameter("bubblemessage");
    var bubbletype = getUrlParameter("bubbletype");
    if(bubblemessage != false) {
        bubblemessage = bubblemessage.replace(/<(?!br\s*\/?)[^>]+>/g, '');
        if(bubbletype == "warning") {
            alert_warning(bubblemessage);
        }
        else if(bubbletype == "info") {
            alert_info(bubblemessage);
        }
        else if(bubbletype == "success") {
            alert_success(bubblemessage);
        }
        else {
            alert_error(bubblemessage);
        }
    }
    

    $(".bulksenduncheck").click(function (e) {
        if(lastCheckbox == true) {
            $('input:checkbox').attr('checked','checked');
        } else {
            $('input:checkbox').removeAttr('checked');
        }
        lastCheckbox = !lastCheckbox;
    });
    $(".bulkactiontoggle").click(function (e) {
        $('input:radio').each(function (i, obj) {
            var skipped = false;
            var matchon = ["skip"];
            if(lastCheckbox == true) {
                matchon = ["update"];
            }
            skipped = matchon.includes($(obj).val());
            if(skipped == false) {
                $(obj).click();
            }
        });
        lastCheckbox = !lastCheckbox;
    });
    
    $(".ajaxonpageload").each(function (i, obj) {
        setTimeout(dynamic_ajax_load, (300 + Math.floor(Math.random() * 400)), $(this));
    });
    $('.inputwithlimit').each(function (i, obj) {
        var focusedID = $(obj).attr('id');
        var hasMin = $("#"+focusedID+"").data("lengthmin");
        var minCount = -1;
        if(hasMin == null) {
            hasMin = false;
        } else {
            hasMin = true;
            minCount = $("#"+focusedID+"").data("lengthmin");
        }
        var maxCount = $("#"+focusedID+"").data("lengthmax");

        if(maxCount == minCount) {
            $("#"+focusedID+"label").append('<p class="d-inline textcounterblock ml-4"><small id="'+$(obj).attr('id')+'usedinput" class="text-muted">Requires [<span id="'+$(obj).attr('id')+'max">-</span>]: <span id="'+$(obj).attr('id')+'current">-</span></small></p>');
        }
        else if(hasMin == false) {
            $("#"+focusedID+"label").append('<p class="d-inline textcounterblock ml-4"><small id="'+$(obj).attr('id')+'usedinput" class="text-muted">used: <span id="'+$(obj).attr('id')+'current">-</span> of <span id="'+$(obj).attr('id')+'max">-</span></small></p>');
        } else {
            $("#"+focusedID+"label").append('<p class="d-inline textcounterblock ml-4"><small id="'+$(obj).attr('id')+'usedinput" class="text-muted">used: <span id="'+$(obj).attr('id')+'current">-</span> of <span id="'+$(obj).attr('id')+'max">-</span> [Min: '+minCount+']</small></p>');
        }
        
    });
    attachInputFocusCounters();
    $(".confirmDialog").click(function (e) {
        var actionMessage = $(this).data('actionmessage');
        var actionText = $(this).data('actiontext');
        var actionEndpoint = $(this).data('targetendpoint');
        var actionTitle = $(this).data('actiontitle');
        $("#confirmModalForm").attr('action',actionEndpoint);
        $("#confirmModalButtonText").text(actionText);
        $("#confirmModalContent").html(actionMessage);
        $("#confirmModalTitle").text(actionTitle);
        $("#confirmModal").modal('show');
    });
    $(".avatarfinderajax").submit(function (e) {
        e.preventDefault();
        if (ajax_busy === false) {
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
    $(".ajaxAndCloseModal").submit(function (e) {
        e.preventDefault();
        $("#confirmModal").modal('hide');
        ajaxForm($(this));
    });
    $(".ajax").submit(function (e) {
        e.preventDefault();
        ajaxForm($(this));
    });
});

function ajaxForm(form)
{
    if (ajax_busy == false) {
        ajax_busy = true;
        var url = form.attr('action');
        var method = form.attr('method');
        var timeout = 3000;
        if ($(this).hasClass("slow") === true) {
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
                            if (jsondata.status === true) {
                                if (jsondata.message != "") alert_success(jsondata.message);
                            }
                            else {
                                redirectdelay = 3500;
                                if (jsondata.message != "") alert_warning(jsondata.message);
                            }
                        }
                        if (jsondata.hasOwnProperty('redirect')) {
                            if (jsondata.redirect != null) {
                                jsondata.redirect = jsondata.redirect.replace("here", "");
                                var urlgoto = SITE_URL + jsondata.redirect;
                                setTimeout(function () { $(location).attr('href', urlgoto) }, redirectdelay);
                            } else {
                                setTimeout(function () { ajax_busy = false }, 1000);
                            }
                            
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
}

$('#NotecardModal').on('click', function (event) {
    var button = $(event.target); // Button that triggered the modal
    var rentaluid = button.data('rentaluid'); // Extract info from data-* attributes
    if(rentaluid.length < 4)
    {
        alert_error("No rental uid given unable to resend.");
        return;
    }
    $.ajax({
        type: "post",
        url: SITE_URL + "Client/Resend/" + rentaluid,
        success: function (data) {
            try {
                jsondata = JSON.parse(data);
                if (jsondata.hasOwnProperty('status')) {
                    alert_success(jsondata.message);
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
            alert_error("something broke :(");
        }
    });
});

$('#NotecardModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var rentaluid = button.data('rentaluid'); // Extract info from data-* attributes
    if(rentaluid.length < 4)
    {
        alert_error("No rental uid given unable to display notecard.");
        return;
    }
    var modal = $(this);
    modal.find('#ModalTitle').text('Loading');
    modal.find('#ModalText').val("Fetching notecard for rental " + rentaluid);
    $.ajax({
        type: "post",
        url: SITE_URL + "Client/Getnotecard/" + rentaluid,
        success: function (data) {
            try {
                jsondata = JSON.parse(data);
                var redirectdelay = 1500;
                if (jsondata.hasOwnProperty('status')) {
                    if (jsondata.hasOwnProperty('message')) {
                        modal.find('#ModalTitle').text('Notecard');
                        modal.find('#ModalText').val(jsondata.message);
                        modal.find('#resendNotecard').attr("data-rentaluid", jsondata.rentaluid);
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
            alert_error("something broke :(");
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
                        if (jsondata.hasOwnProperty('message') === true) {
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
