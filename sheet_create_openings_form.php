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

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

$contextid      = optional_param('contextid', 0, PARAM_INT);                // determines what course
$sheet_group_id = clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN);
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$day            = optional_param('day', '', PARAM_INT); 
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);


//$sheets = getOwnedSheetsAndAssociatedInfo($sheet_group_id,$sheet_id);
$sheet = getStructuredSheetData($sheet_id);

//$sheet = $sheets[0];

print_header_simple("Signup Sheets - Set Up Openings", "", '', "", "", false, "&nbsp;", '');

$day_y = substr($day,0,4);
$day_m = substr($day,4,2);
$day_d = substr($day,6,2);

?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/ui/jquery-ui.js"></script>
<script type="text/javascript" src="js/sus_lib.js"></script>
<div id="sus_user_notify"></div>
<div id="sus_custom_alert"><h1 class="alert_title"></h1><div class="alert_text"></div><div><input type="button" value="close" id="custom_alert_close"/></div></div>
<div id="opening_create_edit">
<?php
//echo '<pre>';print_r($sheet);echo '</pre>';
?>
<form action="sheet_create_openings_process.php" method="POST">
<h2>Creating openings on <?php echo "$day_y-$day_m-$day_d"; ?></h2>
<!--<div class="sus_spacer"></div>-->
<div class="optional_opening_fields_show">show optional fields</div>
<div class="optional_opening_fields">
<div class="optional_opening_fields_hide">hide optional fields</div>
<label for="opening_name">Name: </label>
<input type="text" name="opening_name" id="opening_name" value="" maxlength="255"/>
<br/>
<label for="opening_description">Description: </label>
<textarea name="opening_description" id="opening_description" class="text_entry"></textarea>
<br/>
<label for="opening_admin_comment">Admin Notes:<br/>
<span class="label_more_info">(only the sheet admin can see these)</span></label>
<textarea name="opening_admin_comment" id="opening_admin_comment" class="text_entry"></textarea>
<br/>
<label for="opening_location">Location: </label>
<input type="text" name="opening_location" id="opening_location" value="" maxlength="255"/>
<div class="spacer" style="clear:both; height:15px"></div>
</div><!-- end optional_opening_fields -->


<label for="begintime_hour"><span class="openings_by_time_range">From:</span><span class="openings_by_duration">Starting At:</span></label>
<select name="begintime_hour" id="begintime_hour"><?php echo getOptions(1,1,12,1,1); ?></select>:<select name="begintime_minute"  id="begintime_minute"><?php echo getOptions(0,0,55,5,2); ?></select>
<select name="begintime_ampm" id="begintime_ampm"><option value="am">am</option><option value="pm" selected="selected">pm</option></select>
<span id="opening_spec_toggler"><span class="openings_by_time_range">switch to openings by duration</span><span class="openings_by_duration">switch to openings by time range</span></span>
<input type="hidden" name="opening_spec_type" id="opening_spec_type" value="by_time_range"/>
<br/>
<div class="openings_by_time_range">
<label for="endtime_hour">To:</label>
<select name="endtime_hour" id="endtime_hour"><?php echo getOptions(2,1,12,1,1); ?></select>:<select name="endtime_minute"  id="endtime_minute"><?php echo getOptions(0,0,55,5,2); ?></select>
<select name="endtime_ampm" id="endtime_ampm"><option value="am">am</option><option value="pm" selected="selected">pm</option></select>
</div>

<div class="openings_by_duration">
<label for="durationEachOpening">Make each opening</label>
<select name="durationEachOpening" id="durationEachOpening">
<?php echo getOptions(5,5,90,5); ?>
</select> minutes
</div>

<label for="numOpeningsInTimeRange"># Openings:</label>
<select name="numOpeningsInTimeRange" id="numOpeningsInTimeRange">
<?php echo getOptions(1,1,24); ?>
</select>
<br/>



<label for="numSignsupsPerOpening">Max. Sign-ups per Opening:</label>
<select name="numSignupsPerOpening" id="numSignupsPerOpening">
<option value="-1" >unlimited</option>
<?php echo getOptions(1,1,30); ?>
</select>
<br/>

    <script type="text/javascript">

    $(function()
    {
         $(".optional_opening_fields_show").click(function()
         {
             window.resizeBy(0, $(".optional_opening_fields").height())
             $(".optional_opening_fields").show();
             $(".optional_opening_fields_show").hide();
         });
         $(".optional_opening_fields_hide").click(function()
         {
             $(".optional_opening_fields").hide();
             window.resizeBy(0, -1 * $(".optional_opening_fields").height())
             $(".optional_opening_fields_show").show();
         });

         $("#opening_spec_toggler").click(function()
         {
             $(".openings_by_time_range").toggle();
             $(".openings_by_duration").toggle();
             if ($("#opening_spec_type").attr("value") == "by_time_range")
             {
                 $("#opening_spec_type").attr("value","by_duration")
             } else
             {
                 $("#opening_spec_type").attr("value","by_time_range")
             }
         });

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

         $(".toggler_dow").click(function(event){
                var which = event.target.id.substr(4,3);
                //alert("which is "+which);
                if (event.target.style.background == '')
                {
                    //alert("turning on #repeat_dow_"+which);
                    event.target.style.background = '#aaa';
                    $("#repeat_dow_"+which).attr("value",1);
                } else
                {
                    //alert("turning off #repeat_dow_"+which);
                    event.target.style.background = '';
                    $("#repeat_dow_"+which).attr("value",0);
                }
          });

         $(".toggler_dom").click(function(event){
                var which = event.target.id.substr(8,3);
                //alert("which is "+which);
                if (event.target.style.background == '')
                {
                    event.target.style.background = '#aaa';
                    $("#repeat_dom_"+which).attr("value",1);
                } else
                {
                    event.target.style.background = '';
                    $("#repeat_dom_"+which).attr("value",0);
                }
          });

          var lastRepRate = 1;
          var wdayHeight =  $("#repeatWeekdayChooser").height() + $("#repeatUntilDate").height() + 30;
          var mdayHeight =  $("#repeatMonthdayChooser").height() + $("#repeatUntilDate").height() + 30;
          function resizeWindowForRepRate(newRepRate)
          {
              if (lastRepRate == 2)
              {
                  window.resizeBy(0, -1 * wdayHeight);
              } else if (lastRepRate == 3)
              {
                  window.resizeBy(0, -1 * mdayHeight);
              }

              if (newRepRate == 2)
              {
                  window.resizeBy(0, wdayHeight);
              } else if (newRepRate == 3)
              {
                  window.resizeBy(0, mdayHeight);
              }

              lastRepRate = newRepRate;
          }

          $("#radioOpeningRepeatRate1").click(function(event){
                //alert("on 1");
                $("#repeatWeekdayChooser").hide();
                $("#repeatMonthdayChooser").hide();
                $("#repeatUntilDate").hide();
                resizeWindowForRepRate(1);
          });

          $("#radioOpeningRepeatRate2").click(function(event){
                //alert("on 2");
                $("#repeatWeekdayChooser").show();
                $("#repeatMonthdayChooser").hide();
                $("#repeatUntilDate").show();
                resizeWindowForRepRate(2);
          });

          $("#radioOpeningRepeatRate3").click(function(event){
                //alert("on 3");
                $("#repeatWeekdayChooser").hide();
                $("#repeatMonthdayChooser").show();
                $("#repeatUntilDate").show();
                resizeWindowForRepRate(3);
          });


          $("#btn_save_openings").click(function(event) {
                if (  ($("#endtime_hour").val() == '12')
                    && ($("#endtime_minute").val() == '0')
                    && ($("#endtime_ampm").val() == 'am'))
                {
                    customAlert("","cannot end an opening at 12:00 AM");
                    return false;
                }

                // create start time string
                // create end time string
                var btime = valsToTimeString($("#begintime_hour").val(),$("#begintime_minute").val(),$("#begintime_ampm").val());
                var etime = valsToTimeString($("#endtime_hour").val(),$("#endtime_minute").val(),$("#endtime_ampm").val());
                //alert("time strings are "+btime+" and "+etime);
                
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


<label for="">Repeating?</label>
<div id="repeaterControls">

<div id="chooseRepeatType">
<ul>
<li><input type="radio" id="radioOpeningRepeatRate1" name="openingRepeatRate" value="1" checked="checked"/>Only on <?php echo "$day_y-$day_m-$day_d"; ?></li>
<li><input type="radio" id="radioOpeningRepeatRate2" name="openingRepeatRate" value="2"/>Repeat on days of the week</li>
<li><input type="radio" id="radioOpeningRepeatRate3" name="openingRepeatRate" value="3"/>Repeat on days of the month</li>
</ul>
</div>

<div id="repeatWeekdayChooser">
 <input type="hidden" name="repeat_dow_sun" id="repeat_dow_sun" value="0" />
 <input type="hidden" name="repeat_dow_mon" id="repeat_dow_mon" value="0" />
 <input type="hidden" name="repeat_dow_tue" id="repeat_dow_tue" value="0" />
 <input type="hidden" name="repeat_dow_wed" id="repeat_dow_wed" value="0" />
 <input type="hidden" name="repeat_dow_thu" id="repeat_dow_thu" value="0" />
 <input type="hidden" name="repeat_dow_fri" id="repeat_dow_fri" value="0" />
 <input type="hidden" name="repeat_dow_sat" id="repeat_dow_sat" value="0" />
 <input type="button" id="btn_mon" value="MON" class="toggler_dow" />
 <input type="button" id="btn_tue" value="TUE" class="toggler_dow" />
 <input type="button" id="btn_wed" value="WED" class="toggler_dow" />
 <input type="button" id="btn_thu" value="THU" class="toggler_dow" />
 <input type="button" id="btn_fri" value="FRI" class="toggler_dow" /><br/>
 <input type="button" id="btn_sat" value="SAT" class="toggler_dow" />
 <input type="button" id="btn_sun" value="SUN" class="toggler_dow" />
</div>

<div id="repeatMonthdayChooser">
<?php
for ($md=1;$md<=31;$md++)
{
  echo '<input type="hidden" name="repeat_dom_'.$md.'" id="repeat_dom_'.$md.'" value="0" />'."\n";
  echo '<input type="button" id="btn_dom_'.$md.'" value="'.$md.'" class="toggler_dom" />'."\n";
  if ($md % 7 == 0)
  {
    echo "<br/>\n";
  }
}
?>
</div>

<div id="repeatUntilDate">
until <input type="text" name="until_date" class="sus_choose_date" id="text_until_date" value="<?php echo ymd($sheet->s_date_closes,'-'); ?>"/>
</div>
</div> <!-- end repeaterControls -->

<input type="hidden" name="contextid" value="<?php echo $contextid; ?>"/>
<input type="hidden" name="sheet_group" value="<?php echo $sheet_group_id; ?>"/>
<input type="hidden" name="sheet" value="<?php echo $sheet_id; ?>"/>
<input type="hidden" name="day" value="<?php echo $day; ?>"/>
<input type="hidden" name="action" value="<?php echo $action; ?>"/>

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
<div id="action_button_box">
 <input class="sus_action_button_large" id="btn_save_openings" type="submit" value="Save" name="action_save"/>
 <input class="sus_action_button_large" id="btn_cancel" type="button" value="Cancel" name="action_cancel"/>
</div>

</form>
</div>
</body>
</html>