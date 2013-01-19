/*globals $ */

$(document).ready(function () {
    "use strict";
    /* 
     * Highlighter (terms agreement or like)
     * * * * * * * * * * * * * * * * * * * * */
    $('.highlight').live("click", function (e) {
        var area = $(this).attr('data-area'),
            width = $(area).width();

        $(area).stop(true, true).animate({
            'background-position-x': width
        }, 750, function () {
            $(area).css("background-position-x", '');
        });

        e.preventDefault();
    });
});

/* 
 * Fade screen and display loader 
 * (a = message, b = true/false, whether to show overlay or not)
 * * * * * * * * * * * * * * * * * * * * */
function setLoading(a, b) {
    $("body").append('<div id="loading_overlay"></div><div id="loading_loader">' + a + "</div>");
    if (b) {
        $("#loading_overlay").css("opacity", 0.15).fadeIn(function () {
            $("#loading_loader").fadeIn();
        });
    } else {
        $("#loading_loader").fadeIn();
    }
}

/* 
 * Fades out the loaders and overlay, and removes the loader elements
 * * * * * * * * * * * * * * * * * * * * */
function removeLoading() {
    $("#loading_loader").fadeOut("fast", function () {
        $("#loading_loader").remove();
        $("#loading_overlay").animate({
            opacity: 0
        }, function () {
            $(this).remove();
        });
    });
}

/* 
 * Slides up the notification, to hide it.
 * * * * * * * * * * * * * * * * * * * * */
var notificationInt = null;
function hideNotification() {
    clearInterval(notificationInt);
    $("#alertMessage").animate({
        top: -$("#alertMessage").height()
    }, 500);
}

/* 
 * display a sldie now notification, useful for ajax responses.
 * a = title
 * b = body
 * c = notification type: error, success, warning, info.
 * sticky = true/false - keep the notification at the top until the user closes it.
 * * * * * * * * * * * * * * * * * * * * */
function showNotification(a, b, c, sticky) {
    hideNotification();

    if (sticky === 'undefined') {
        sticky = false;
    }

    if (b === 'undefined') {
        b = '';
    } else {
        b = '<h4 class="alert-heading">' + b + '</h4>';
    }

    switch (c) {
    case "error":
        c = "alert-error";
        break;
    case "info":
        c = "alert-info";
        break;
    case "success":
        c = "alert-success";
        break;
    default:
        c = "";
        break;
    }

    var position = $("#alertMessage").position();
    $("#alertMessage").html('<div class="alert ' + c + '"><a class="close" data-dismiss="alert" href="#">&times;</a>' + b + a + "</div>").stop(true, true).animate({
        top: 0
    }, 500, function () {
        if (!sticky) {
            notificationInt = setInterval(hideNotification, 5000);
        }
    });
}