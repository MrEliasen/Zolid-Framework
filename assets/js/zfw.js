$(document).ready(function(){

	//Bind all ajax forms to actually send an ajax request rather than post/get
	$('form[method="ajax"]').submit(function(e){
        var form = $(this);
		setLoading('Please wait..');
		$.ajax({
			method: 'POST',
			data: form.serialize(),
			dataType: 'JSON',
			success: function(reply){
				// Show any notifications available.
				if(typeof reply.message_body != 'undefined') {
					showNotification(reply.message_body, reply.message_title, reply.message_type);
				}

				// Redirect the user if required.
				if(typeof reply.redirect != 'undefined') {
					location.href=reply.redirect;
                    return;
				}

                // Reset the form after a successfull submission.
                if(typeof reply.reset != 'undefined') {
                    form[0].reset();
                }

				removeLoading();
			}
		});

		e.preventDefault();
		return false;
	});

    // when we click the mailbox item, load the mail body.
    var lastMsg = 0;
    $('#mailbox > a').click(function(e){
        var el = $(this);
        if(lastMsg == $(this).data('id')) {
            return;
        }
        setLoading('Please wait..');
        $.ajax({
            method: 'POST',
            data: 'action=getmail&msgid=' + $(this).data('id'),
            dataType: 'JSON',
            success: function(reply){
                $('#mailcontent').fadeOut(function(){
                    // Show any notifications available.
                    if(typeof reply.message_body != 'undefined') {
                        showNotification(reply.message_body, reply.message_title, reply.message_type);
                    } else {
                        lastMsg = el.data('id');
                        if( el.find('.glyphicon').length ){
                            // decrease the unread messages counter
                            $('#unread').html( parseInt($('#unread').html()) - 1 );
                            // remove the unread message notification icon from the message.
                            el.find('.glyphicon').remove();
                        }
                        $(this).html('<div class="well">' + reply.message + '</div><input type="hidden" name="token" value="' + reply.token + '"><input type="hidden" name="msgid" value="' + reply.id + '"><button class="btn btn-danger btn-lg">Delete Message</button>' ).fadeIn();
                    }
                });
                removeLoading();
            }
        });
        e.preventDefault();
        return false;
    });
    
    // Deletion of mailbox messages
    $('#deletemessage').submit(function(e){
        var el = $(this);
        setLoading('Please wait..');
        $.ajax({
            method: 'POST',
            data: el.serialize(),
            dataType: 'JSON',
            success: function(reply){
                if(reply.status) {
                    $('#mailbox').find('a[data-id=' + el.find('input[name=msgid]').val() + ']').fadeOut(function(){
                        $(this).remove();
                    });
                    $('#mailcontent').fadeOut(function(){
                        $(this).html('');
                    });
                }

                showNotification(reply.message_body, reply.message_title, reply.message_type);
                removeLoading();
            }
        });
        e.preventDefault();
        return false;
    });
});

/* 
 * Fade screen and display loader 
 * (a = message, b = true/false, whether to show overlay or not)
 * * * * * * * * * * * * * * * * * * * * */
function setLoading(a, b) {
    $("body").append('<div id="loading_overlay"></div><div id="loading_loader">' + a + "</div>");
    if (b) {
        $("#loading_overlay").css("opacity", 0.15).fadeIn('fast', function () {
            $("#loading_loader").fadeIn('fast');
        });
    } else {
        $("#loading_loader").fadeIn('fast');
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
    if ( b != '' ) {
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
    $("#alertMessage").html('<div class="alert ' + c + '"><button type="button" class="close">&times;</button>' + b + a + "</div>").stop(true, true).animate({
        top: 0
    }, 500, function () {
        if (!sticky) {
            notificationInt = setInterval(hideNotification, 5000);
        }
    });
}

/* 
 * Remove the notification when you click on it (or the X)
 * * * * * * * * * * * * * * * * * * * * */
$('#alertMessage').click(function(){
    hideNotification();
});