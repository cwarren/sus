<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

$DEBUG=3;

//debug_r(1,$USER);
//exit;

////////////////////////////////////////////////////////////////////////////////////////

$sheet_id = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$access_id = clean_param($_REQUEST['access'],PARAM_CLEAN);

$sheet_complex = getStructuredSheetData($sheet_id);

if (! $sheet_complex) // no sheets found, something went wrong
{
    error("failed to find sheet $sheet_id");
}

// verify the user is actually allowed to see this sheet based on that record.
// Otherwise a logged in user could just enter random sheet ids in the URL to signup for those sheets
if (! userHasSignupAccess($USER->id,$sheet_id,$access_id))
{
  echo "Sorry - you do not have signup access to this sheet";
  exit;
}

// DESIGN NOTE: the page content section is organized as two main areas:
//    1. a small, full width area across the top that holds the general sheet info
//    2. a large full with area below that which has two tabs.
//      2.1. first tab is a calendar view on left and day detail on the right
//      2.2. second tab is a list view (no detail view needed)

?>
<div id="sus_user_notify"></div>
<div id="sus_custom_alert"><h1 class="alert_title"></h1><div class="alert_text"></div><div><input type="button" value="close" id="custom_alert_close"/></div></div>
<div class="sus_sheet_data">
<style type="text/css">
 @import url("tab.css");
</style>
<script type="text/javascript" src="js/tabber.js"></script>

<div id="sus_signup_on_sheet_info">
  <div id="sheet_name"><?php cleanecho($sheet_complex->s_name,true);?></div>
  <div id="sheet_description"><?php cleanecho($sheet_complex->s_description,true);?></div>
  <div id="sheet_further_info">
    This sheet is in the <?php cleanecho($sheet_complex->group->sg_name,true);?> group.

    You may have 
	<?php cleanecho(sus_grammatical_max_signups($sheet_complex->group->sg_max_g_total_user_signups), true); ?>  	
	across all sheets in this group (<?php cleanecho(sus_grammatical_max_signups($sheet_complex->group->sg_max_g_pending_user_signups), true); ?>  
	may be for future times). 
    Currently you have 
<span class="signup_count_in_group"><?php echo $USER->signups['total']['groups'][$sheet_complex->group->sg_id]+0; ?></span> in this group, 
<span class="signup_count_in_group_future"><?php echo $USER->signups['future']['groups'][$sheet_complex->group->sg_id] + 0; ?></span> of which are in the future.



     You may have
	<?php cleanecho(sus_grammatical_max_signups($sheet_complex->s_max_total_user_signups), true); ?>  
        on this sheet (<?php cleanecho(sus_grammatical_max_signups($sheet_complex->s_max_pending_user_signups), true); ?>
        may be for future times). Currently you have 
<span class="signup_count_on_sheet"><?php echo $USER->signups['total']['sheets'][$sheet_complex->s_id] + 0; ?></span> on this sheet, 
<span class="signup_count_on_sheet_future"><?php echo $USER->signups['future']['sheets'][$sheet_complex->s_id] + 0; ?></span> of which are in the future.

  </div>
</div>

<div id="sus_signup_on_sheet_openings">

<div class="tabber">
 <div class="tabbertab" title="Openings as Calendar">


<?php 

// get first of the month of the starting date - this is the cal start date
// get the last of the month of the ending date - this is the cal end date

include_once 'cal_lib.php';

//$openings = getOpenings($sheet_complex_id);
//getCalendar($sheet_complex,openings,'','',true);

getCalendar($sheet_complex,'','',true);

?>

<div class="signup_cal_right_col">
  <div id="day_openings_full_details">
  </div>

  <div id="signup_help">
    <div class="signup_help_text">
<b>Overview</b>
<p>To the left is a calendar showing all openings for this sheet. Hover
over an openings icon <img src="image/example_opening_icon.jpg" width="10" height-"13"/> to
see a summary of the openings on that day, and click on that icon to
get a more detailed list (which replaces this help text). In either
case, click "sign up" to sign up for a given opening, or <img
src="<?php echo "image/pix/t/delete.png";?>" class="iconsmall" alt="remove
signup" title="remove signup"/> to remove yourself from a given
opening.
</p>
<p>
To see all the openings for this sheet in a text-based list format instead of the graphical calendar display, click the "Openings as List" tab above.
</p>
    </div>
  </div>  

</div>

  <br clear="all">
  
 </div>
 <div class="tabbertab" title="Openings as List">
  <div class="opening_list_format">
<?php 
//getOpeningsList($openings,'','',true);
if ($sheet_complex->openings[0])
{
  getOpeningsList($sheet_complex->openings,ymd($sheet_complex->s_date_opens),'','',true,$sheet_complex->s_flag_private_signups);
} else
{
  echo "This sheet doesn't yet have any openings";
}
?>
  </div>
 </div>
</div>

</div><!-- end sus_signup_on_sheet_openings -->

</div><!-- end sus_sheet_data -->

<script type="text/javascript">
$(document).ready(function()
{

  function handleAddSignupLinkState()
  {
    $(".opening_signup_link").unbind("click");
    if (   ( ($(".signup_count_in_group").html() < <?php echo $sheet_complex->group->sg_max_g_total_user_signups + 0; ?>) 
            || (<?php echo $sheet_complex->group->sg_max_g_total_user_signups + 0; ?> < 1) )
        && ( ($(".signup_count_in_group_future").html() < <?php echo $sheet_complex->group->sg_max_g_pending_user_signups + 0; ?>)
            || (<?php echo $sheet_complex->group->sg_max_g_pending_user_signups + 0; ?> < 1) )
        && ( ($(".signup_count_on_sheet").html() < <?php echo $sheet_complex->s_max_total_user_signups + 0; ?>)
            || (<?php echo $sheet_complex->s_max_total_user_signups + 0; ?> < 1) )
        && ( ($(".signup_count_on_sheet_future").html() < <?php echo $sheet_complex->s_max_pending_user_signups + 0; ?>)
            || (<?php echo $sheet_complex->s_max_pending_user_signups + 0; ?> < 1) )
       )
    {
      $(".opening_signup_link").removeClass("disabled_link");
      $(".opening_signup_link").click(function(evt)
      {
        //alert("click!");
        handleSignupClick(evt);
      });
    } else
    {
      $(".opening_signup_link").addClass("disabled_link");
      customAlert("Signup Limit","You've reached the limit on sign-ups for this sheet or group. See the sheet description at the top of this page for details.");
      //notifyUser("You've hit your limits on sign-ups for this sheet or group.<br/>See the description at the top of this page for details",false);

      $(".opening_signup_link").click(function(evt)
      {
        evt.stopPropagation();
        return false;
      });
    }
  }

  // takes: a class name for a signup count, an adjustment number (1, or -1)
  // does: sets the value and highlighting for that count
  function adjustSignupCountsValAndHighlight(which,adj)
  {    
    var val = Number($("."+which).html()) + adj;
    $("."+which).html(val);
    var valmax = 0;
    switch (which) 
    {
      case "signup_count_in_group": 
        valmax = <?php echo $sheet_complex->group->sg_max_g_total_user_signups + 0; ?>; break;
      case "signup_count_in_group_future": 
        valmax = <?php echo $sheet_complex->group->sg_max_g_pending_user_signups + 0; ?>; break;
      case "signup_count_on_sheet": 
        valmax = <?php echo $sheet_complex->s_max_total_user_signups + 0; ?>; break;
      case "signup_count_on_sheet_future": 
        valmax = <?php echo $sheet_complex->s_max_pending_user_signups + 0; ?>; break;
    }

    if ( (val < valmax) || (valmax < 1) )
    {
      $("."+which).removeClass("highlight_bad");
      $("."+which).addClass("highlight_good");
    } else
    {
      $("."+which).removeClass("highlight_good");
      $("."+which).addClass("highlight_bad");
    }
  }

  // called here to get the highligting right
  adjustSignupCountsValAndHighlight("signup_count_in_group",0);
  adjustSignupCountsValAndHighlight("signup_count_on_sheet",0);
  adjustSignupCountsValAndHighlight("signup_count_in_group_future",0);
  adjustSignupCountsValAndHighlight("signup_count_on_sheet_future",0);

  ////////////////////////////////////////////////////////////////////////////////
  // custom alert
/*  $("#custom_alert_close").click(function()
  {
    $("#sus_custom_alert").stop(true,true);
    $("#sus_custom_alert").css("left","-999");
  }); */

  ////////////////////////////////////////////////////////////////////////////////
  // calendar clicking and mousing

  var last_clicked_day = '';
  var hightlight_color = "#f0c633";
  var highlight_border = "3px solid "+hightlight_color;
  var last_day_bg = '';
  var hightlight_day_bg = hightlight_color;

  $(".day_openings_minitimes").hover(
    function() {
      try
      {
        var oon_id = "#openings_on_"+$(this).attr("for_date");
        //alert("oon_id is " + oon_id);
        //$(".openings_summary_box").css("left","-999px");
        $(oon_id).css("top",$(this).position().top);
        $(oon_id).css("left",$(this).position().left + $(this).width() + 4);
      }
      catch(err)
      {
      }
    },
    function() {
      try
      {
        var oon_id = "#openings_on_"+$(this).attr("for_date");
        //alert("oon_id is " + oon_id);
        $(oon_id).css("left","-999px");
      }
      catch(err)
      {
        $(".openings_summary_box").css("left","-999px");
      }
    }
  );

  // highlighting and info display of full opening details for a given day
  $(".day_openings_minitimes").click(function(event)
  {
    // put the contents of the associated openings_on div into the full details div
    $("#day_openings_full_details").empty();
    $("#day_openings_full_details").append( $("#openings_on_"+$(this).attr("for_date")).html() );
    // make sure the new opening_signup_links are registered
    handleAddSignupLinkState();
//    $("#day_openings_full_details .opening_signup_link").click(function(evt)
//    {
//      handleSignupClick(evt);
//    });
    $("#day_openings_full_details .remove_signup_link").click(function(evt)
    {
      handleRemoveSignupClick(evt);
    });

    // shift the hightlighting as appropriate
    if (last_clicked_day != '')
    {
      $("#day_"+last_clicked_day).css("background",last_day_bg);
    }
    last_clicked_day = $(this).attr("for_date");
    last_border = $(this).css("border");
    $("#day_openings_full_details").css("border",highlight_border);
    $("#day_"+last_clicked_day).css("background",hightlight_day_bg);
    
    // hide the help text
    $("#signup_help").hide();

    // consume the event here so it doesn't prop on to the day cell (code below)
    event.stopPropagation();
  });


  $(".day_cell").click(function()
  {
    if (last_clicked_day != '')
    {
      $("#day_"+last_clicked_day).css("background",last_day_bg);
      $("#day_openings_full_details").css("border","none");
      $("#day_openings_full_details").html('');

      // show the help text
      $("#signup_help").show();
    }    
  });

  ////////////////////////////////////////////////////////////////////////////////
  // sign up for an opening, or remove signup for an opening
  handleAddSignupLinkState();
  $(".remove_signup_link").click(function(evt)
  {
    //alert("click!");
    handleRemoveSignupClick(evt);
  });

  function handleSignupClick(evt)
  {
    $("#sus_user_notify").stop(true,true);
    //alert("evt target parent is "+$(evt.target).parent().html()); // DEBUGGING
    //$(evt.target).parent().css("border","1px dashed blue"); // DEBUGING
    var opg = $(evt.target).parent().attr("for_opening");
    $.ajax({
      url: 'handle_signups_ajax.php',
      //url: 'blahblah.php',
      cache: false,
      data: {contextid: <?php echo $context->id;?>,
             access: <?php echo $access_id;?>,
             sheet: <?php echo $sheet_id;?>,
             opening: opg,
             action: "addsignup",
             actionsource: "do_signup"},
      error:  function(theRequest, textStatus, errorThrown)
      {
        notifyUser("SAVE FAILED!<br/>error connecting to the server",false);
      },
      success:  function(data, textStatus)
      {
        if (data.match(/^SUCCESS/))
        {
          notifyUser("Signed up!");
          // TODO: update info on the page to reflect that signup
          $(".opening_"+opg).replaceWith(data.substring(7));
          adjustSignupCountsValAndHighlight("signup_count_in_group",1);
          adjustSignupCountsValAndHighlight("signup_count_on_sheet",1);
          if ($(".opening_"+opg).attr("is_future") == "yes")
          {
            adjustSignupCountsValAndHighlight("signup_count_in_group_future",1);
            adjustSignupCountsValAndHighlight("signup_count_on_sheet_future",1);
          }
          handleAddSignupLinkState();
          //$(".opening_"+opg+" .opening_signup_link").click(function(evt)
          //{
          //  handleSignupClick(evt);
          //});
          $(".opening_"+opg+" .remove_signup_link").click(function(evt)
          {
            handleRemoveSignupClick(evt);
          });
        } else
        {
          notifyUser("SIGNUP ABORTED!<br/>"+data,false);
        }
       }
    });
    // consume the event here so it doesn't prop on to the mini display)
    evt.stopPropagation();
  }

  function handleRemoveSignupClick(evt)
  {
    $("#sus_user_notify").stop(true,true);
    //alert("evt target parent is "+$(evt.target).parent().html()); // DEBUGGING
    //$(evt.target).parent().css("border","1px dashed blue"); // DEBUGING
    var opg = $(evt.target).attr("for_opening");
    var su = $(evt.target).attr("for_signup");
    $.ajax({
      url: 'handle_signups_ajax.php',
      //url: 'blahblah.php',
      cache: false,
      data: {contextid: <?php echo $context->id;?>,
             access: <?php echo $access_id;?>,
             sheet: <?php echo $sheet_id;?>,
             opening: opg,
             signup: su,
             action: "removesignup",
             actionsource: "do_signup"},
      error:  function(theRequest, textStatus, errorThrown)
      {
        notifyUser("SAVE FAILED!<br/>error connecting to the server",false);
      },
      success:  function(data, textStatus)
      {
        if (data.match(/^SUCCESS/))
        {
          notifyUser("Signup removed");
          // TODO: update info on the page to reflect that signup
          $(".opening_"+opg).replaceWith(data.substring(7));
          adjustSignupCountsValAndHighlight("signup_count_in_group",-1);
          adjustSignupCountsValAndHighlight("signup_count_on_sheet",-1);
          if ($(".opening_"+opg).attr("is_future") == "yes")
          {
            adjustSignupCountsValAndHighlight("signup_count_in_group_future",-1);
            adjustSignupCountsValAndHighlight("signup_count_on_sheet_future",-1);
          }
          handleAddSignupLinkState();
          //$(".opening_"+opg+" .opening_signup_link").click(function(evt)
          //{
          //  handleSignupClick(evt);
          //});
          $(".opening_"+opg+" .remove_signup_link").click(function(evt)
          {
            handleRemoveSignupClick(evt);
          });
        } else
        {
          notifyUser("REMOVE ABORTED!<br/>"+data,false);
        }
       }
    });
    // consume the event here so it doesn't prop on to the mini display)
    evt.stopPropagation();
  }

  $("#sus_user_notify").css("left",700);
  $("#sus_user_notify").css("top",100);
});
</script>