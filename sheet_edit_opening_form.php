<?php

require_once('../../config.php');
require_once($CFG->libdir.'/moodlelib.php');

/////////////////////////////////////////////////////////////////////////////////////////
// locally defined functions

// This function returns true. It is called by various included files
// to make sure they're actually in the signup sheet world - i.e. to
// prevent them from being (usefully) used directly from the web. E.g.
// if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }
function verify_in_signup_sheets() {
    return true;
}

require_login();

include_once 'sus_lib.php';

$DEBUG = 3;

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

$contextid      = optional_param('contextid', 0, PARAM_INT);                // determines what course
$sheet_group_id = clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN);
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id     = clean_param($_REQUEST['opening'],PARAM_CLEAN);
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);
$day            = optional_param('day', '', PARAM_INT); 

//print_r($_REQUEST);
//exit;

if (! userHasAdminAccess($USER->id,$sheet_id))
{
  echo "You do not have permissions to edit openings on this sheet";
  exit;
}

//$opening_ar = getOpenings($sheet_id,$opening_id,0,true);
$ssd = getStructuredSheetData($sheet_id,0,$opening_id);

//print_r($ssd->openings[0]); exit;
//$opening = $opening_ar[0];

print_header_simple("Signup Sheets - Edit Opening", "", '', "", "", false, "&nbsp;", '');

$day_y = substr($day,0,4);
$day_m = substr($day,4,2);
$day_d = substr($day,6,2);

echo '<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/signup_sheets/js/sus_lib.js"></script>';
?>
<div id="sus_user_notify"></div>
<div id="sus_custom_alert"><h1 class="alert_title"></h1><div class="alert_text"></div><div><input type="button" value="close" id="custom_alert_close"/></div></div>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/ui/jquery-ui.js"></script>
<style type="text/css" media="print">
 @import url("<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/print_opening_styles.css");
</style>
<div class="for_opening_printing">
<?php
echo "<div class=\"sus_very_small\">".sus_moodle_name()." Sign-up Sheet - Opening Print-out</div>\n";
echo "<h2>{$ssd->s_name}</h2>\n";
echo "<h5>{$ssd->s_description}</h5>\n";
if ($ssd->openings[0]->o_name)
{
    echo "<h1>{$ssd->openings[0]->o_name}</h1>\n";
}
if ($ssd->openings[0]->o_description)
{
    echo "<h4>{$ssd->openings[0]->o_description}</h4>\n";
}
if ($ssd->openings[0]->o_admin_comment)
{
    echo "<h4><i>{$ssd->openings[0]->o_admin_comment}</i></h4>\n";
}
echo "<h1>$day_y-$day_m-$day_d from ".hmi_a($ssd->openings[0]->o_begin_datetime,'-')." to ".hmi_a($ssd->openings[0]->o_end_datetime,'-').".</h1>\n";
if ($ssd->openings[0]->o_location)
{
    echo "<h1>Meeting at {$ssd->openings[0]->o_location}</h1>\n";
}
?>
</div><!-- end for_printing -->
<form class="sus_opening_print_button_form"><input type="button" class="sus_action_button_large" value="Print this opening" onclick="window.print();return false;" /></form>

<div id="opening_create_edit">
<div class="left_col">
<form action="sheet_edit_opening_process.php" method="POST">
<h2>Edit Opening</h2>
<input type="hidden" name="contextid" value="<?php echo $contextid; ?>"/>
<input type="hidden" name="sheet_group" value="<?php echo $sheet_group_id; ?>"/>
<input type="hidden" name="sheet" value="<?php echo $sheet_id; ?>"/>
<input type="hidden" name="day" value="<?php echo $day; ?>"/>
<input type="hidden" name="action" value="updateopening"/>
<input type="hidden" name="opening" value="<?php echo $opening_id; ?>"/>
<input type="hidden" name="opening_set" value="<?php echo $ssd->openings[0]->o_opening_set_id; ?>"/>

<!--<h2>Edit opening on <?php echo ymd($ssd->openings[0]->o_begin_datetime,'-'); ?> at <?php echo hmi_a($ssd->openings[0]->o_begin_datetime); ?></h2>-->
<div class="sus_spacer"></div>

<label for="opening_name">Name (optional): </label>
<input type="text" name="opening_name" id="opening_name" class="text_entry" value="<?php echo $ssd->openings[0]->o_name?>" maxlength="255"/>
<br/>
<label for="opening_description">Description (optional): </label>
<textarea name="opening_description" id="opening_description" class="text_entry"><?php echo $ssd->openings[0]->o_description?></textarea>
<br/>
<label for="opening_admin_comment">Admin Notes:<br/>
<span class="label_more_info">(optional; only the sheet admin can see these)</span></label>
<textarea name="opening_admin_comment" id="opening_admin_comment" class="text_entry"><?php echo $ssd->openings[0]->o_admin_comment?></textarea>
<br/>
<label for="opening_location">Location (optional): </label>
<input type="text" name="opening_location" id="opening_location"  class="text_entry" value="<?php echo $ssd->openings[0]->o_location?>" maxlength="255"/>
<br/>

<label for="on_date">On: </label><input type="text" name="on_date" class="sus_choose_date" id="text_on_date" value="<?php echo "$day_y-$day_m-$day_d"; ?>"/>
<br/>

<label for="begintime_hour"><span class="openings_by_time_range">From:</span><span class="openings_by_duration">Starting At:</span></label>
<select name="begintime_hour" id="begintime_hour"><?php echo getOptions(date("g",$ssd->openings[0]->o_begin_datetime),1,12,1,1); ?></select>:<select name="begintime_minute"  id="begintime_minute"><?php echo getOptions(date("i",$ssd->openings[0]->o_begin_datetime),0,55,5,2); ?></select>
<select name="begintime_ampm" id="begintime_ampm">
  <option value="am"<?php echo (date("H",$ssd->openings[0]->o_begin_datetime)<12)?' selected="selected"':'';?>>am</option>
  <option value="pm"<?php echo (date("H",$ssd->openings[0]->o_begin_datetime)>=12)?' selected="selected"':'';?>>pm</option>
</select>
<br/>
<div class="openings_by_time_range">
<label for="endtime_hour">To:</label>

<select name="endtime_hour" id="endtime_hour"><?php echo getOptions(date("g",$ssd->openings[0]->o_end_datetime),1,12,1,1); ?></select>:<select name="endtime_minute"  id="endtime_minute"><?php echo getOptions(date("i",$ssd->openings[0]->o_end_datetime),0,55,5,2); ?></select>
<select name="endtime_ampm" id="endtime_ampm">
  <option value="am"<?php echo (date("H",$ssd->openings[0]->o_end_datetime)<12)?' selected="selected"':'';?>>am</option>
  <option value="pm"<?php echo (date("H",$ssd->openings[0]->o_end_datetime)>=12)?' selected="selected"':'';?>>pm</option>
</select>
</div>

<label for="numSignsupsPerOpening">Maximum Sign-ups:</label>
<select name="numSignupsPerOpening" id="numSignupsPerOpening">
<option value="-1" <?php if ($ssd->openings[0]->o_max_signups<1) {echo 'selected="selected"';}?>>unlimited</option>
<?php echo getOptions($ssd->openings[0]->o_max_signups,1,30); ?>
</select>
<br/>

    <script type="text/javascript">

    $(function()
    {
         $(".sus_choose_date").datepicker({ dateFormat: 'yy-m-d'
                                      , changeMonth: true
                                      , changeYear: true
                                      , closeText: 'X'
                                      , hideIfNoPrevNext: true
                                      , nextText: '&gt;'
                                      , prevText: '&lt;'
                                      , showButtonPanel: true
                                      , showOtherMonths: true
                                      , yearRange: '-1:+1'});

         $("#ui-datepicker-div").hide(); // UGLY HACK!!!!

          $("#btn_save_openings").click(function(event) {
                if (  ($("#endtime_hour").val() == '12')
                    && ($("#endtime_minute").val() == '0')
                    && ($("#endtime_ampm").val() == 'am'))
                {
                    customAlert("","cannot end an opening at 12:00 AM");
                    return false;
                }

                var btime = valsToTimeString($("#begintime_hour").val(),$("#begintime_minute").val(),$("#begintime_ampm").val());
                var etime = valsToTimeString($("#endtime_hour").val(),$("#endtime_minute").val(),$("#endtime_ampm").val());
                // if end <= start, that's a problem
                if (etime <= btime)
                {
                    customAlert("","end time must be later than start time");
                    return false;
                }

                return true;
          });


    });

    </script>

<style type="text/css">
 @import url("<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/ui/ui-css/ui-lightness/jquery-ui-1.7.2.custom.css");
</style>


    <script type="text/javascript">

    $(function()
    {
          $("#btn_cancel").click(function(event){
                //alert("cancelling");
                window.close();
                return false;
          });
    });

    </script>
</div><!-- end left col -->
<div class="right_col">
  <h2>Sign-ups<div id="listsorters"><a id="signupnamesort">Sort by last name</a> | <a id="signuptimesort">Sort by sign-up order</a></div></h2>
<?php
echo '<input type="hidden" name="num_signups" id="num_signups"  value="'.count($ssd->openings[0]->signups)."\"/>\n";
echo '<ul class="opening_signees_list" id="opening_signees_list_display" for_opening="'.$ssd->openings[0]->o_id.'">'."\n";
?>
  <li id="signup_someone_list_item">sign someone up</li>
  <li id="signup_someone_data_entry">
   <label for="new_signup_username" class="wide_label">Username:</label><input type="text" name="new_signup_username" id="new_signup_username" value="" class="text_entry"/><br/>
   <label for="new_signup_admin_comment" class="wide_label">Admin Note:</label><textarea name="new_signup_admin_comment" class="su_admin_comment" id="new_signup_admin_comment"></textarea><br/>
   <input class="sus_action_button" id="btn_new_signup" type="button" value="Sign them up"/>
   <input class="sus_action_button" id="btn_cancel_new_signup" type="button" value="Cancel"/>
  </li>
<?php
$printing_opening_list = "<ul class=\"opening_signees_list for_opening_printing\" id=\"opening_signees_list_print\">\n";
if ($ssd->openings[0]->signups)
{
    foreach ($ssd->openings[0]->signups as $su)
    {
        echo "  <li class=\"signee_list_item\" signup_id=\"{$su->su_id}\" id=\"signee_list_item_for_{$su->su_id}\" lname=\"{$su->user->usr_lastname}\" fname=\"{$su->user->usr_firstname}\" signuptime=\"{$su->su_created_at}\" >";
        echo " <img class=\"remove_signup_link nukeit\" src=\"image/pix/t/delete.png\" alt=\"remove signup\" title=\"remove signup\" for_signup=\"{$su->su_id}\" for_opening=\"{$su->su_sus_opening_id}\"/>";
        echo "{$su->user->usr_firstname} {$su->user->usr_lastname}\n";
        echo "   <span class=\"sus_very_small\">(".ymd_hm_a($su->su_created_at,'-').")</span><br/>\n";
        if ($su->su_created_at != $su->su_created_at)
        {
            echo "   <span class=\"sus_very_small\">updated ".ymd_hm_a($su->su_created_at,'-')."</span><br/>\n";
        }
        echo "    <label for=\"su_admin_comment_{$su->su_id}\">Admin Note:</label><textarea name=\"su_admin_comment_{$su->su_id}\" class=\"su_admin_comment\" id=\"su_admin_comment_{$su->su_id}\">{$su->su_admin_comment}</textarea>";
        echo "  </li>\n";
        $printing_opening_list .= "<li class=\"for_opening_printing\">{$su->user->usr_lastname}, {$su->user->usr_firstname} <span class=\"sus_very_small\">(".ymd_hm_a($su->su_created_at,'-').")</span>";
        if ($su->su_admin_comment)
        {
            $printing_opening_list .= "<br/><div class=\"signup_admin_comment\">{$su->su_admin_comment}</div>";
        }
        $printing_opening_list .= "</li>\n";
    }
    $printing_opening_list .= "</ul>\n";
}

echo " <li id=\"no_signups_list_item\">No sign-ups for this opening</li>";

?>
</ul>

</div><!-- end right col -->

<br clear="all"/>
<div id="action_button_box">
 <input class="sus_action_button_large" id="btn_save_openings" type="submit" value="Save" name="action_save"/>
 <input class="sus_action_button_large" id="btn_cancel" type="button" value="Cancel" name="action_cancel"/>
</div>

</form>

<div class="spacer_bg"></div>

<script type="text/javascript">
$(function()
{
    $("#signup_someone_list_item").click(function()
    {
      $("#signup_someone_list_item").css("display","none");
      $("#signup_someone_data_entry").css("display","block");
      $("#new_signup_username").focus();
    });
    $("#btn_cancel_new_signup").click(function()
    {
      $("#new_signup_username").val('');
      $("#new_signup_admin_comment").val('');
      $("#signup_someone_list_item").css("display","block");
      $("#signup_someone_data_entry").css("display","none");
    });

    $("#btn_new_signup").click(function(evt)
    {
      //alert("$(#new_signup_username).val().match(/\S/) is "+$("#new_signup_username").val().match(/\S/));
      if ($("#new_signup_username").val().match(/\S/) != null)
      {
        //alert("saving");

        $.ajax({
          url: 'handle_signups_ajax.php',
          //url: 'blahblah.php',
          cache: false,
          data: {contextid: <?php echo $contextid;?>,
                 sheet: <?php echo $sheet_id;?>,
                 opening: <?php echo $opening_id;?>,
                 action: "addsignup",
                 username: $("#new_signup_username").val(),
                 admincomment: $("#new_signup_admin_comment").val(),
                 actionsource: "sheet_edit_opening_form"},
          error:  function(theRequest, textStatus, errorThrown)
          {
            notifyUser("SAVE FAILED!<br/>error connecting to the server: "+theRequest,false);
          },
          success:  function(data, textStatus)
          {
            if (data.match(/^SUCCESS/))
            {
              notifyUser("Signed up!");
              // update info on the page to reflect that signup
              $("#num_signups").val($("#num_signups").val()*1 + 1);
              var res_data = data.split('___|||---'); // NOTE: that is the seperator defined in the ajax handler
              var new_su_id = res_data[0].substring(7); // omit 'SUCCESS'
              var new_su_first_name = res_data[1];
              var new_su_last_name = res_data[2];
	      var new_su_full_name = new_su_first_name+' '+new_su_last_name;
              var new_su_admin_comment = res_data[3];
	      var new_su_time = res_data[4];
              var new_signee_item =
"  <li class=\"signee_list_item\" signup_id=\""+new_su_id+"\" id=\"signee_list_item_for_"+new_su_id+"\" lname=\""+new_su_last_name+"\" fname=\""+new_su_first_name+"\" signuptime=\""+new_su_time+"\" >" +
" <img class=\"remove_signup_link nukeit\" src=\"image/pix/t/delete.png\" alt=\"remove signup\" title=\"remove signup\" for_signup=\""+new_su_id+"\" for_opening=\"<?php echo $opening_id; ?>\"/>" +
new_su_full_name + "\n" +
"   <span class=\"sus_very_small\">(signed up <?php echo ymd_hm_a('','-'); ?>)</span><br/>\n" +
"    <label for=\"su_admin_comment_"+new_su_id+"\">Admin Note:</label><textarea name=\"su_admin_comment_"+new_su_id+"\" class=\"su_admin_comment\" id=\"su_admin_comment_"+new_su_id+"\">"+new_su_admin_comment+"</textarea>" +
"  </li>\n";
              $("#opening_signees_list_display").append(new_signee_item);
              $("#signee_list_item_for_"+new_su_id+" .remove_signup_link").click(function(evt) // hook up delete code
              {
                handleSignupRemoval(evt);
              });
              handleNumSignups();
              $("#btn_cancel_new_signup").click();
            } else
            {
              notifyUser("SIGNUP ABORTED!<br/>"+data,false);
            }
          }});

      } else // no username given
      {
        $("#btn_cancel_new_signup").click();
      }
    });

    function handleNumSignups()
    {
      if ($("#num_signups").val() < 1)
      {
        $("#no_signups_list_item").css('display','block');
      } else
      {
        $("#no_signups_list_item").css('display','none');
      }
    }
    handleNumSignups(); // call the above func on page load
    
    $(".remove_signup_link").click(function(evt)
    {
      handleSignupRemoval(evt);
    });

    function handleSignupRemoval(evt)
    {
      $("#sus_user_notify").stop(true,true);

      var opg = $(evt.target).attr("for_opening");
      var su = $(evt.target).attr("for_signup");
      $.ajax({
        url: 'handle_signups_ajax.php',
        //url: 'blahblah.php',
        cache: false,
        data: {contextid: <?php echo $contextid;?>,
               access: '',
               sheet: <?php echo $sheet_id;?>,
               opening: opg,
               signup: su,
               action: "removesignup",
               actionsource: "sheet_edit_opening_form"},
        error:  function(theRequest, textStatus, errorThrown)
        {
          notifyUser("SAVE FAILED!<br/>error connecting to the server",false);
        },
        success:  function(data, textStatus)
        {
          if (data.match(/^SUCCESS/))
          {
            notifyUser("Signup removed");
            // TODO: update info on the page to reflect that signup being removed
            $("#num_signups").val($("#num_signups").val() - 1);
            $("#signee_list_item_for_"+su).remove();
            handleNumSignups();
          } else
          {
            notifyUser("REMOVE ABORTED!<br/>"+data,false);
          }
         }
      });

      // consume the event here so it doesn't prop on to the mini display)
      evt.stopPropagation();
    }


    // make sure both subject and body are non-blank
    function validateEmailFields()
    {
        //alert('$("#email_text").val() is |'+$("#email_text").val()+'|');
        //alert('$("#email_text").val().match("/\S/") is |'+$("#email_text").val().match("/\S/")+'|');
        //alert('$("#email_subject").val() is |'+$("#email_subject").val()+'|');
        //alert('$("#email_subject").val().match("/\S/") is |'+$("#email_subject").val().match("/\S/")+'|');
        var has_subj = $("#email_text").val().match(/\S/) != null;
        var has_text = $("#email_subject").val().match(/\S/) != null;
        if ((has_subj) && (has_text))
        {
            return true;
        }
        customAlert("","Emails must have both subject and text");
        return false;
    }

    $("#btn_send_email").click(function()
    {
        if (! validateEmailFields())
        {
            return false;
        }
        //alert('sending');

        // make an ajax call to a send email ajax handler
        //   author is used as primary to address, others are bcc'd       email_cc_author: ($("#email_cc_me").attr("checked")=="checked")?1:0 ,
        $.ajax({
          url: 'send_email_ajax.php',
          //url: 'blahblah.php',
          cache: false,
          data: {contextid: <?php echo $contextid;?>,
                 sheet: <?php echo $sheet_id;?>,
                 opening: <?php echo $opening_id;?>,
                 email_subject:  $("#email_subject").val(),
                 email_body: $("#email_text").val(),
                 action: "sendemail",
                 actionsource: "sheet_edit_opening_form"},
          error:  function(theRequest, textStatus, errorThrown)
          {
            notifyUser("EMAIL FAILED!<br/>error connecting to the server",false);
          },
          success:  function(data, textStatus)
          {
            if (data.match(/^SUCCESS/))
            {
              notifyUser("Email sent");
              $("#email_subject").val("<?php echo preg_replace('/"/',"'",$ssd->s_name) . " - " . ymd_hm_a($ssd->openings[0]->o_begin_datetime,'-'); ?>");
              $("#email_text").val('');
              $("#email_cc_me").attr("checked","checked");
            } else
            {
              notifyUser("EMAIL ABORTED!<br/>"+data,false);
            }
           }
        });
    });

    function sortSignupsAlpha(a,b)
    {
      return ($(a).attr('lname')+$(a).attr('fname') > $(b).attr('lname')+$(b).attr('fname')) ? 1 : -1;  
    };
    function sortSignupsTime(a,b)
    {
      return ($(a).attr('signuptime') > $(b).attr('signuptime')) ? 1 : -1;  
    };

    $("#signupnamesort").click(function() {
	$('#opening_signees_list_display li.signee_list_item').sort(sortSignupsAlpha).appendTo('ul.opening_signees_list');
      });

    $("#signuptimesort").click(function() {
	$('#opening_signees_list_display li.signee_list_item').sort(sortSignupsTime).appendTo('ul.opening_signees_list');
      });




    // check incoming action, if it's add a signup, click the add signup link
<?php
if ($action=="addsomeone")
{
    echo '$("#signup_someone_list_item").click();';
}
?>

  ////////////////////////////////////////////////////////////////////////////////
  // custom alert
/*  $("#custom_alert_close").click(function()
  {
    $("#sus_custom_alert").stop(true,true);
    $("#sus_custom_alert").css("left","-999");
  });*/

});
</script>

<div id="opening_send_email">
<form id="opening_send_email_form"
 <h2>Send email to all signed up for this opening</h2>
 <br/>
 <label for="email_subject">Subject: </label>
 <input type="text" name="email_subject" id="email_subject"  class="text_entry" value="<?php echo preg_replace('/"/',"'",$ssd->s_name) . " - " . ymd_hm_a($ssd->openings[0]->o_begin_datetime,'-'); ?>" maxlength="255"/>
 <br/>
 <label for="email_text">Message: </label>
 <textarea name="email_text" id="email_text" class="text_entry"></textarea>
<?php
//author is used as primary to address, others are bcc'd
// <br/>
// <label for="email_text">CC me: </label>
// <input type="checkbox" name="email_cc_me" id="email_cc_me" value="1" checked="checked"> 
?>
 <div id="action_button_box">
  <input class="sus_action_button_large" id="btn_send_email" value="Send" name="action_send"/>
 </div>

</form>

</div><!-- end opening_send_email -->

</div><!-- end opening_create_edit -->

<?php

echo $printing_opening_list;

?>

</body>
</html>