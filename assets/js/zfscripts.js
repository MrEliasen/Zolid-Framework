/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

 // Just a small fix to make "hashchange" work with jQ 1.9+ //Mark Eliasen
var msie = false;
if (navigator.appName == 'Microsoft Internet Explorer') {
    msie = true;
}
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidsolutions.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.5
 */
 $(function(){
    //Load the tab which is selected in the address hash
    $('a[href="'+window.location.hash.replace('/', '')+'"]').tab("show");
    
    //when you click a tab, change the address hash
    $('a[data-toggle="tab"]').click(function(){
        window.location.hash = '/'+$(this).attr('href').replace('#', '');
    });
});

var baseUrl = $('meta[name=baseurl]').attr('content'),
    userToken = $('meta[name=usertoken]').attr('content');

$(document).ready(function () {
    "use strict";

    /* 
     * Show the drag elements if we are on the admin page
     * * * * * * * * * * * * * * * * * * * * */
    if($('.dd').length){
        $('.dd').nestable({ maxDepth: 1, }).on('change', saveforumorder);
    }

    /* 
     * Hide notification when you click on it.
     * * * * * * * * * * * * * * * * * * * * */
    $('#alertMessage').click(function(){
        hideNotification();
    });

    /* 
     * Bootstrap Tooltips
     * * * * * * * * * * * * * * * * * * * * */
    $('*[data-toggle="tooltip"]').tooltip();

    /* 
     * Bootstrap Popovers
     * * * * * * * * * * * * * * * * * * * * */
    $('*[data-toggle="popover"]').popover({ html: true });
    $(document).on("click", ".closepo", function(e){
        $('*[data-toggle="popover"]').popover('hide');
    });

    /* 
     * Zolid Framework Modals
     * * * * * * * * * * * * * * * * * * * * */
    $(document).on("click", "*[data-toggle=modal]", function(e){
        var button = $(this);
        $.get(baseUrl + '/?p=ajax&a=modal_' + button.attr('data-loadmodal') + '&id=' + button.attr('data-id'), function(data) {
            $('#modal').modal();
            $('#modal').html(data);
        }).success(function() {
            $('input:text:visible:first').focus();
            $('#savechanges').click(function(e){
                if($("#loading_loader").is(':visible')){
                    return false;
                }
                setLoading('Processing..');
                $.ajax({
                    url: baseUrl + '/?p=ajax&a=' + button.attr('data-action'),
                    type: 'POST',
                    dataType: "json",
                    data: $('#modal form').serialize(),
                    success: function(reply){
                        removeLoading();
                        if(reply.status){
                            $('#modal').modal('hide');
                            showNotification(reply.message, '', 'success');
                            if(typeof reply.add !== 'undefined'){
                                var addto = '';
                                if( button.closest('.tab-pane').find('tbody').length ){
                                    addto = button.closest('.tab-pane').find('tbody');
                                }else{
                                    addto = $(reply.addto);
                                }
                                addto.append(reply.add);
                                $('*[data-toggle="popover"]').popover({ html: true });
                            }
                            if(typeof reply.sendto !== 'undefined' && reply.sendto != ''){
                                if(parseInt(reply.page) == parseInt($('.pagination .active a').html()) || !$('.pagination .active a').length || reply.sendto == 'force'){
                                    setTimeout((function(){window.location.reload(true)}), 1500);
                                }else{
                                    setTimeout((function(){location.href=reply.sendto}), 1500);
                                }
                            }
                        }else{
                            showNotification(reply.message, '', 'error');
                        }
                    }
                });
                e.preventDefault();
                return false;
            });
        });
        e.preventDefault();
    });

    /* 
     * Admin delete account
     * * * * * * * * * * * * * * * * * * * * */
    $(document).on("click", ".funcdelete", function(e){
        var button = $(this);
        $.ajax({
            url: baseUrl + '/?p=ajax&a=' + button.attr('data-action'),
            type: 'POST',
            dataType: "json",
            data: 'id=' + button.attr('data-id') + '&usertoken=' + userToken,
            success: function(reply){
                if(reply.status){
                    showNotification(reply.message, '', 'success');
                    $('#row_' + button.attr('data-target')).fadeOut('fast', function(){
                        $(this).remove();
                    });
                    if(typeof reply.sendto !== 'undefined' && reply.sendto != ''){
                        if(parseInt(reply.page) == parseInt($('.pagination .active a').html()) || !$('.pagination .active a').length || reply.sendto == 'force'){
                            setTimeout((function(){window.location.reload(true)}), 1500);
                        }else{
                            setTimeout((function(){location.href=reply.sendto}), 1500);
                        }
                    }
                }else{
                    showNotification(reply.message, '', 'error');
                }
            }
        });
        e.preventDefault();
    });

    /* 
     * Admin save settings
     * * * * * * * * * * * * * * * * * * * * */
    $('#saveSettings').click(function(e){
        $.ajax({
            url: baseUrl + '/?p=ajax&a=savesettings',
            type: 'POST',
            dataType: "json",
            data: $('#settings form').serialize() + '&usertoken=' + userToken,
            success: function(reply){
                if(reply.status){
                    showNotification(reply.message, '', 'success');
                }else{
                    showNotification(reply.message, '', 'error');
                }
            }
        });
        e.preventDefault();
        return false;
    });

    /* 
     * Highlighter (terms agreement or like)
     * * * * * * * * * * * * * * * * * * * * */
    $(document).on("click", ".highlight", function(e){
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
 * Saves the forum category display order
 * * * * * * * * * * * * * * * * * * * * */
function saveforumorder() {
    if(window.JSON){
        $('#savestatus-loading').show();
        var neworder = window.JSON.stringify($('#forumlist').nestable('serialize'));
        $.ajax({
            url: baseUrl + '/?p=ajax&a=saveforumorder',
            type: 'POST',
            dataType: "json",
            data: 'usertoken=' + userToken + '&neworder=' + neworder,
            success: function(reply){
                $('#savestatus-loading').hide();
                if(reply.status){
                    $('#savestatus-ok').stop(true, true).fadeIn('fast');
                    setTimeout((function(){$('#savestatus-ok').fadeOut('fast')}), 1000);
                }else{
                    showNotification(reply.message, '', 'error');
                    $('#savestatus-err').stop(true, true).fadeIn('fast');
                    setTimeout((function(){$('#savestatus-err').fadeOut('fast')}), 1000);
                }
            }
        });
    }else{
        showNotification('JSON browser support required, please use a more modern browser.', 'Error', 'error');
        $('#savestatus-err').stop(true, true).fadeIn('fast');
        setTimeout((function(){$('#savestatus-err').fadeOut('fast')}), 1000);
    }
}

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

    if (b === 'undefined' || b == '' ) {
        b = '';
    } else {
        b = '<h4 class="alert-heading">' + b + '</h4>';
    }

    switch (c) {
        case "error":
            c = "alert-danger";
            break;
        case "info":
            c = "alert-info";
            break;
        case "success":
            c = "alert-success";
            break;
        default:
            c = "alert-warning";
            break;
    }

    var position = $("#alertMessage").position();
    $("#alertMessage").html('<div class="alert ' + c + '"><button type="button" class="close" data-dismiss="alert">&times;</button>' + b + a + "</div>").stop(true, true).animate({
        top: 0
    }, 500, function () {
        if (!sticky) {
            notificationInt = setInterval(hideNotification, 5000);
        }
    });
}