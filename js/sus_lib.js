function notifyUser(msg, newsIsGood, container, leftOffset, topOffset){
    // msg is the message to display to the user
    // newsIsGood determine the style of msg box
    // container is an HTML element that the msg box is positioned compared to
    // leftOffset and topOffset allow fine control over where the msg box displays

    if (newsIsGood === undefined){
	newsIsGood = true;
    }
    if (container === undefined){
	container = '#sus_user_notify';
    }
    if (leftOffset === undefined){
	leftOffset = 0;
    }
    if (topOffset === undefined){
	topOffset = 0;
    }

    if (newsIsGood){
	$("#sus_user_notify").css('background', '#BEB');
        $("#sus_user_notify").css('height', '50px');
    }
    else{
	$("#sus_user_notify").css('background', '#EBB');
        $("#sus_user_notify").css('height', '90px');
    }

    $("#sus_user_notify").html(msg);
    $("#sus_user_notify").css("left",$(container).position().left+leftOffset);
    $("#sus_user_notify").css("top",$(container).position().top+topOffset);
    $("#sus_user_notify").fadeIn(150,function(){$("#sus_user_notify").fadeTo(1500,1,function(){$("#sus_user_notify").fadeOut(450);})});
}


// displays a "window" in the center of the browser window. The window has a close button, but also a time so it will auto-close eventually
function customAlert(title,msg)
{
    // title is the text to show in the title bar - set to '' if no title is desired
    // msg is the main text to display
    var duration = (msg.length + title.length) * 55 + 500;
    $("#sus_custom_alert .alert_title").html(title);
    $("#sus_custom_alert .alert_text").html(msg);
    //alert("$(#sus_custom_alert).width() is "+$("#sus_custom_alert").width());
    //var x = document.body.clientWidth / 2 - $("#sus_custom_alert").width() /2;
    var myWidth = 0, myHeight = 0;
    if( typeof( window.innerWidth ) == 'number' ) {
	//Non-IE
	myWidth = window.innerWidth;
	myHeight = window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
	//IE 6+ in 'standards compliant mode'
	myWidth = document.documentElement.clientWidth;
	myHeight = document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
	//IE 4 compatible
	myWidth = document.body.clientWidth;
	myHeight = document.body.clientHeight;
    }
    var x = myWidth / 2 - $("#sus_custom_alert").width() /2;
    var y = myHeight / 3;
    $("#sus_custom_alert").css("left",x);
    $("#sus_custom_alert").css("top",y);
    $("#sus_custom_alert").css("display","block");

    $("#custom_alert_close").click(function() {
	$("#sus_custom_alert").stop(true,true);
	$("#sus_custom_alert").css("left","-999");
    });

    $("#sus_custom_alert").fadeIn(50,function(){$("#sus_custom_alert").fadeTo(duration,1,function(){$("#sus_custom_alert").fadeOut(150);})});


}



// takes: an hour, minute, and am/pm string
// returns: a single string of the time usable in comparisons (later is larger)
function valsToTimeString(hr,mi,ap)
{
    //alert("params are "+hr+","+mi+","+ap);
    var num_hr = parseInt(hr);
    var num_mi = parseInt(mi);
    if ((ap == 'pm') && (hr != '12'))
	{
	    num_hr += 12;
	}
    return (num_hr<10?"0":"") + num_hr.toString() + (num_mi<10?"0":"") + num_mi.toString();
}


