<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

$DEBUG=0;

////////////////////////////////////////////////////////////////////////////////////////

$sheet_group_id = clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN);
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id     = '';
if (isset($_REQUEST['opening']))
{
    $opening_id = clean_param($_REQUEST['opening'],PARAM_CLEAN);
}

$user_is_admin = false;

# !!! NOTE: sheet_group_id gets re-set from 'new' to 0 in here!
if ($sheet_id == 'new')
{
    $sheets = newSheet($sheet_group_id, 's_');
    $sheet = $sheets[0];
    $sheet_id = 0;
?>
<script type="text/javascript">
$(document).ready(function() {
    $(".sus_sheet_data_right").hide();
});
</script>
<?php
} else // sheet id is not 'new'
{
    $s_info = array(
        'name' => optional_param('name', 'no name provided'),
        'description' => optional_param('description', ''),
        'type' => 'timeblocks',
        'date_opens' => optional_param('date_opens',''),
        'date_closes' => optional_param('date_closes',''),
        'max_total_user_signups' => optional_param('max_total_signups', '-1'),
        'max_pending_user_signups' => optional_param('max_pending_signups', '-1'),
        'flag_alert_owner_change' => fromCheckbox(optional_param('alert_owner_change',0)),
        'flag_alert_owner_signup' => fromCheckbox(optional_param('alert_owner_signup',0)),
        'flag_alert_owner_imminent' => fromCheckbox(optional_param('alert_owner_imminent',0)),
        'flag_alert_admin_change' => fromCheckbox(optional_param('alert_admin_change',0)),
        'flag_alert_admin_signup' => fromCheckbox(optional_param('alert_admin_signup',0)),
        'flag_alert_admin_imminent' => fromCheckbox(optional_param('alert_admin_imminent',0)));

    $new_group = optional_param('group_id','0');
    if ($new_group)
    {
        $s_info['sus_sheetgroup_id'] = $new_group;
    }

    // convert yyyy-mm-dd dates into timestamps
    if ($s_info['date_opens'])
    {
        $ymd = explode('-',$s_info['date_opens']);
        $s_info['date_opens']=mktime(0,0,0,$ymd[1],$ymd[2],$ymd[0]);
    }
    if ($s_info['date_closes'])
    {
        $ymd = explode('-',$s_info['date_closes']);
        $s_info['date_closes']=mktime(0,0,0,$ymd[1],$ymd[2],$ymd[0]);
    }

    if (isset($_REQUEST['subaction']))
    {
      if ($_REQUEST['subaction'] == 'updatesheet')
      {
          updateSheet($sheet_id,$s_info);

      } elseif ($_REQUEST['subaction'] == 'createsheet')
      {    
          $sheet_id = addSheet($s_info);
      }
    }

    $sheet = getStructuredSheetData($sheet_id);
    if (! $sheet) // no sheets found, something went wrong
    {    
        error("failed to find sheet $sheet_id for $USER->id");
    }

    if ($USER->id != $sheet->s_owner_user_id) 
    {
        if (array_key_exists($USER->username,$sheet->access_controls['keyed_adminbyuser']))
        {
            $user_is_admin = true;
        } else
        {
            error("You are not the owner of this sheet and you do not have admin access");
        }
    }
}

$groups = getSheetGroup();

// DESIGN NOTE: the page content section is organized as three main areas:
//    1. a small, full width area across the top
//    2. a narrower column on the left (which holds the main sheet info & form)
//    3. a wider column on the right (which holds a tabbed panel for block/opening stuff) 

$create_opening_url = $CFG->wwwroot.'/blocks/signup_sheets/sheet_create_openings_form.php?contextid='.$context->id.
                                         '&sheet='.$sheet_id.
                                         '&sheetgroup='.$sheet_group_id;
$edit_opening_url = $CFG->wwwroot.'/blocks/signup_sheets/sheet_edit_opening_form.php?contextid='.$context->id.
                                         '&sheet='.$sheet_id.
                                         '&sheetgroup='.$sheet_group_id;

?>
<div id="sus_user_notify"></div>
<div id="sus_custom_alert"><h1 class="alert_title"></h1><div class="alert_text"></div><div><input type="button" value="close" id="custom_alert_close"/></div></div>

<div class="sus_sheet_data">
<style type="text/css">
 @import url("tab.css");
 @import url("<?php echo $CFG->wwwroot; ?>/blocks/signup_sheets/js/jquery/ui/ui-css/ui-lightness/jquery-ui-1.7.2.custom.css");
</style>

</style>
<script type="text/javascript" src="js/tabber.js"></script>

  <?php
if ($sheet_id)
{
  ?>
  <p class="sus_timestamp_info">This sheet was created on <?php 
  echo date('Y-n-j',$sheet->s_created_at);
  echo ' at '.date('g:i A',$sheet->s_created_at);
  if ($sheet->s_created_at+5 < $sheet->s_updated_at) { 
      echo ", and last changed on ".date('Y-n-j',$sheet->s_updated_at);
      echo ' at '.date('g:i A',$sheet->s_updated_at); 
  }
  ?>.</p>
  <?php
}

  ?>

<div class="sus_sheet_data_left">


<div class="tabber">
 <div class="tabbertab" title="Basic Sheet Info">
  <form action="" method="POST">
    <input type="hidden" name="sheetgroup" value="<?php echo $sheet_group_id; ?>" />
    <input type="hidden" name="sheet" value="<?php echo $sheet_id; ?>" />
    <input type="hidden" name="action" value="editsheet" />
    <input type="hidden" name="subaction" value="<?php echo ($sheet_id)?'updatesheet':'createsheet'; ?>" />
		 

<div class="sus_spacer"></div>
<label for="name" >Sheet Name:</label>
<input id="input_sheet_name" <?php echo $sheet_id?'':'class="sus_temp_data"';?> type="text" name="name" value="<?php cleanecho($sheet->s_name,true);?>" maxlength="255" />

    <div id="sheet_group_id" class="sus_formtext">
    <label for="group_id">In group:</label>
<?php
if ($user_is_admin)
{
    echo $sheet->group->sg_name;
    echo "<br/>";
} else
{
    echo "<select id=\"select_sheet_group_id\" name=\"group_id\">";
    $cur_group_name = '';
    foreach ($groups as $group)
    {
        echo "  <option value=\"$group->sg_id\" ";
        if ($group->sg_id==$sheet_group_id) {
            echo 'selected="selected"';
            $cur_group_name = $group->sg_name;
        }
        echo ">$group->sg_name</option>\n";
    }
    ?></select><br>(see <a href="<?php echo "$edit_group_url$sheet_group_id"; ?>" class="sus_small">"<?php echo $cur_group_name; ?>"</a> group)
<?php
}
?>
</div><!-- end sus_formtext-->
    <br/>



    <div id="sheet_description"><textarea id="text_sheet_description" <?php echo $sheet_id?'':'class="sus_temp_data"';?> name="description"><?php cleanecho($sheet->s_description,true);?></textarea></div>

    <script type="text/javascript">

    //Date.format = 'yyyy/mm/dd';
    $(document).ready(function()
    {

	 $(".sus_choose_date").datepicker({
                                        dateFormat: 'yy-m-d'
                                      , changeMonth: true
                                      , changeYear: true
                                      , closeText: 'X'
                                      , hideIfNoPrevNext: true
                                      , nextText: '&gt;'
                                      , prevText: '&lt;'
                                      , showButtonPanel: true
                                      , showOtherMonths: true
                                      , onClose: constrainPickers
                                      , yearRange: '-4:+4'
                                          });
         $("#ui-datepicker-div").hide(); // UGLY HACK!!!!

<?php
if (isset($sheet->openings) && $sheet->openings) // when there are openings, use the earliest and latest as the min and max boundaries
{
    $omin_parts = explode('-',ymd($sheet->openings[0]->o_begin_datetime,'-')); 
    $omax_parts = explode('-',ymd($sheet->openings[count($sheet->openings)-1]->o_begin_datetime,'-'));
?>
         // NOTE: these are based on the earlier and latest OPENING dates, not the existing sheet dates
         // ALSO NOTE: once the user starts changing the date ranges
         // then these limits vanish. Consider incorporating them into
         // constrainPickers (but then have to deal with new openings
         // being created and old ones deleted - ugh...)

         // 2010-02-11 CSW : removing all this date constrainign mess; the various constraints and defaulting is just causing problems right now
         //var cd = new Date(<?php echo $omax_parts[0];?>,<?php echo $omax_parts[1];?>-1,<?php echo $omax_parts[2];?>);
         //$('#text_date_closes').datepicker('option','minDate',cd);
         //var od = new Date(<?php echo $omin_parts[0];?>,<?php echo $omin_parts[1];?>-1,<?php echo $omin_parts[2];?>);
         //$('#text_date_opens').datepicker('option','maxDate',od);
<?php
}
?>

        function constrainPickers(dateText,inst)
        {
          // alert("constraing dates on "+this.id);
     /* var dparts = dateText.split('-');
          d = new Date(dparts[0],dparts[1]-1,dparts[2]);
          if (this.id == 'text_date_opens')
          {
            $('#text_date_closes').datepicker('option','minDate',d);
          } else
          if (this.id == 'text_date_closes')
          {
            $('#text_date_opens').datepicker('option','maxDate',d);
          }
     */
        }

        // form validation
        $("#save_sheet_button").click(function(event){
          if (! ($("#input_sheet_name").val().match(/\S/)))
          {
            customAlert("Missing Information","You must enter a name for the sheet");
            $("#input_sheet_name").focus();
            return false;
          }
          return true;
        });

 /*       $("#custom_alert_close").click(function()
        {
          $("#sus_custom_alert").stop(true,true);
          $("#sus_custom_alert").css("left","-999");
        }); */

    });
    </script>

<label>Date Span: </label>
<div class="sus_indent">
This sheet is active from <br/>
<input type="text" 
       name="date_opens" 
       class="sus_choose_date" 
       id="text_date_opens" 
       value="<?php echo date('Y-n-j',$sheet->s_date_opens); ?>"
       />
 to 
<input type="text" name="date_closes" class="sus_choose_date" id="text_date_closes" value="<?php echo date('Y-n-j',$sheet->s_date_closes); ?>"/>
</div>

<label>Maximum Sign-ups: </label>
<div class="sus_indent">
Users can have
<select name="max_total_signups" id="select_max_total">

<?php 
//if group is unlimited, offer unlimited for this sheet
//else range is 1 to group limit
//print_r($sheet);
if ((! isset($sheet->group->sg_max_g_total_user_signups)) 
    || (! $sheet->group->sg_max_g_total_user_signups) 
    || ($sheet->group->sg_max_g_total_user_signups < 1))
{
    echo '<option value="-1" ';
    echo (($sheet->s_max_total_user_signups < 0 || (! $sheet->s_max_total_user_signups)) ? 'selected="selected"' : '');
    echo '>unlimited</option>';
    echo getOptions($sheet->s_max_total_user_signups,1,8);
}
else
{
    echo getOptions($sheet->s_max_total_user_signups,1,$sheet->group->sg_max_g_total_user_signups);
}
?>
</select>
signup<?php echo (($sheet->s_max_total_user_signups==1)?'':'s');?> on this sheet, and
<select name="max_pending_signups" id="select_max_pending">
<?php
//if group is unlimited, offer unlimited for this sheet
//else range is 1 to group limit
//echo "sheet->group->sg_max_g_pending_user_signups is {$sheet->group->sg_max_g_pending_user_signups}\n";
if (   (! isset($sheet->group->sg_max_g_pending_user_signups))
    || (! $sheet->group->sg_max_g_pending_user_signups)
    || ($sheet->group->sg_max_g_pending_user_signups == -1))
{
    echo '<option value="-1" ';
    echo (($sheet->s_max_pending_user_signups < 0 || (! $sheet->s_max_pending_user_signups)) ? 'selected="selected"' : '');
    echo '>any</option>';
    echo getOptions($sheet->s_max_pending_user_signups,1,8);
}
else
{
    echo getOptions($sheet->s_max_pending_user_signups,1,$sheet->group->sg_max_g_pending_user_signups);
}
?>
</select>
may be for future openings.
    </div><!-- end sus indent -->

    <div id="sus_sheet_flags">
     <div class="hh1">Email Notifications</div>
      <div id="sus_owner_alerts">
        <div class="hh2">To Owner On</div>
        <!--<div class="alert_option"><input type="checkbox" id="check_alert_owner_change" 
                    name="alert_owner_change"<?php echo($sheet->s_flag_alert_owner_change?'checked="checked"':'');?>>change to sheet</div>-->
        <div class="alert_option"><input type="checkbox" id="check_alert_owner_signup" 
                    name="alert_owner_signup"<?php echo($sheet->s_flag_alert_owner_signup?'checked="checked"':'');?>>signup or cancel</div>
        <div class="alert_option"><input type="checkbox" id="check_alert_owner_imminent" 
                    name="alert_owner_imminent"<?php echo($sheet->s_flag_alert_owner_imminent?'checked="checked"':'');?>>upcoming signup</div>
      </div><!-- end owner alerts -->

      <div id="sus_admin_alerts">
        <div class="hh2">To Admins On</div>
        <!--<div class="alert_option"><input type="checkbox" id="check_alert_admin_change" 
                    name="alert_admin_change"<?php echo($sheet->s_flag_alert_admin_change?'checked="checked"':'');?>>change to sheet</div>-->
        <div class="alert_option"><input type="checkbox" id="check_alert_admin_signup" 
                    name="alert_admin_signup"<?php echo($sheet->s_flag_alert_admin_signup?'checked="checked"':'');?>>signup or cancel</div>
        <div class="alert_option"><input type="checkbox" id="check_alert_admin_imminent" 
                    name="alert_admin_imminent"<?php echo($sheet->s_flag_alert_admin_imminent?'checked="checked"':'');?>>upcoming signup</div>
      </div><!-- end sus admin alerts-->
    </div> <!-- end sus sheet flags -->

    <div id="action_button_box">
      <input type="submit" id="save_sheet_button" class="sus_action_button_med" name="action_save" value="Save" />
      <input type="submit" class="sus_action_button_med" name="action_cancel" value="Cancel" />
    </div>

  </form>
<script type="text/javascript">
document.getElementById("input_sheet_name").focus();
document.getElementById("input_sheet_name").select();
</script>

 </div><!-- end tabbertab -->
<?php
if ($sheet_id != 'new')
{

    $existing_perms = getAccessPermissions($sheet_id);

?>
 <div class="tabbertab" title="Sheet Access">
  <div id="access_to_signup">

    <div class="hh2">Who can see signups</div>
    <div id="signup_privacy_settings">
     <?php
     $check_this = 'checked="checked"';
     $private_checked = $check_this;
     $public_checked = '';
     if ($sheet->s_flag_private_signups == 0)
     {
         $private_checked = '';
         $public_checked = $check_this;
     }
     ?>
     <input class="signup_privacy_control" id="signup_privacy_0" type="radio" name="signup_privacy" value="0" <?php echo $public_checked; ?>> Users can see who signed up when<br/>
     <input class="signup_privacy_control" id="signup_privacy_1" type="radio" name="signup_privacy" value="1" <?php echo $private_checked; ?>> Users can only see their own signups
    </div><!-- end privacy -->

    <div class="hh2">Who can sign up</div>

    <div id="access_by_course">
     <div class="hh3">People in these courses</div>
     <div class="cb_list" id="access_by_course_list">
<?php
$user_courses = get_my_courses($USER->id);
foreach ($user_courses as $user_course)
{
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_bycourse']) && in_array($user_course->id,$existing_perms['data_or_ids_of_bycourse']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_bycourse'][$user_course->id].'"';
  }
  echo '<input type="checkbox" id="access_by_course_'.$user_course->id.'" class="permission_checkbox" '.$checked_and_id.' permtype="bycourse" permval="'.$user_course->id.'">'."$user_course->idnumber : $user_course->fullname<br/>\n";
}
?>
     </div><!-- end cb list -->
    </div><!-- end access by course -->

    <div id="access_by_instr">
     <div class="hh3">People in courses taught by</div>
     <div class="cb_list" id="access_by_instr_list">
<?php
$teachers = getAllTeachers();
foreach ($teachers as $teacher)
{
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_byinstr']) && in_array($teacher->id,$existing_perms['data_or_ids_of_byinstr']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_byinstr'][$teacher->id].'"';
  }
  echo '<input type="checkbox" id="access_by_instr_'.$teacher->id.'"  class="permission_checkbox" '.$checked_and_id.' permtype="byinstr" permval="'.$teacher->id.'">'."$teacher->firstname $teacher->lastname ($teacher->username)<br/>\n";
}
?>
     </div><!-- end cb list -->
    </div><!-- end access by instr -->

    <div id="access_by_user">
     <div class="hh3">These people</div>
     <div class="sus_very_small">seperate usernames by white space and/or commas</div>
     <textarea id="access_by_user_list" class="permission_user_list" permtype="byuser"><?php 
if (isset($existing_perms['data_or_ids_of_byuser']))
{
  sort($existing_perms['data_or_ids_of_byuser']);
  echo implode(", ",$existing_perms['data_or_ids_of_byuser']);
}
?></textarea>
    </div><!-- access by user -->

<?php
###################################
# NOTE: this is commented out because the department and grad year
#  info is Williams specific (see getAllDepartments and
#  getAllGradYears functions in sus_lib) - the code is left in the
#  comments as a template in case you want to implement this on your
#  own system
/*
echo '
   <div id="dept_and_gy_side_by_side">
    <div id="access_by_dept">
     <div class="hh3">People taking a course in</div>
     <div class="cb_list" id="access_by_dept_list">
';

$depts = getAllDepartments();
foreach ($depts as $dept)
{
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_bydept']) && in_array($dept,$existing_perms['data_or_ids_of_bydept']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_bydept'][$dept].'"';
  }
  echo '<input type="checkbox" id="access_by_dept_'.$dept.'" class="permission_checkbox" '.$checked_and_id.' permtype="bydept" permval="'.$dept.'">'."$dept<br/>\n";
}
echo'
     </div><!-- end cb list -->
    </div><!-- end access by dept -->
';

echo '
    <div id="access_by_gradyear">
     <div class="hh3">People with a grad year of</div>
     <div class="cb_list" id="access_by_gradyear_list">
';
$gradyears = getAllGradYears();
foreach ($gradyears as $gradyear)
{
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_bygradyear']) && in_array($gradyear,$existing_perms['data_or_ids_of_bygradyear']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_bygradyear'][$gradyear].'"';
  }
  echo '<input type="checkbox" id="access_by_gradyear_'.$gradyear.'" class="permission_checkbox" '.$checked_and_id.' permtype="bygradyear" permval="'.$gradyear.'">'."$gradyear<br/>\n";
}
echo '
     </div><!-- end cb list -->
    </div><!-- end grad yr -->
   </div><!-- end dept_and_gy_side_by_side -->
';
*/
?>

 <br style="clear:both;"/> 

    <div id="access_by_role">
     <div class="hh3">People who are a</div>
     <div id="access_by_role_list">
<?php
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_byrole']) && in_array('teacher',$existing_perms['data_or_ids_of_byrole']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_byrole']['teacher'].'"';
  }
  echo '<input type="checkbox" id="access_by_role_teacher" class="permission_checkbox" '.$checked_and_id.' permtype="byrole" permval="teacher">teacher of a course<br/>'."\n";
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_byrole']) && in_array('student',$existing_perms['data_or_ids_of_byrole']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_byrole']['student'].'"';
  }
  echo '<input type="checkbox" id="access_by_role_student" class="permission_checkbox" '.$checked_and_id.' permtype="byrole" permval="student">student in a course<br/>'."\n";
  $checked_and_id = '';
  if (isset($existing_perms['data_or_ids_of_byhasaccount']) && in_array('all',$existing_perms['data_or_ids_of_byhasaccount']))
  {
    $checked_and_id = 'checked="checked" permid="'.$existing_perms['keyed_byhasaccount']['all'].'"';
  }
  echo '<input type="checkbox" id="access_by_any" class="permission_checkbox" '.$checked_and_id.' permtype="byhasaccount" permval="all">'.sus_moodle_name().' user'."\n";
?>
     </div><!-- end role list -->
    </div><!-- end access by role -->

  </div><!-- end signup access -->


<?php
if ($user_is_admin)
{
    if (isset($existing_perms['data_or_ids_of_adminbyuser']))
    {
?>
  <div id="access_to_admin">
    <div class="hh2">Who can manage the sheet</div>
    <div id="admin_by_user">
<?php
        sort($existing_perms['data_or_ids_of_adminbyuser']);
        echo implode(", ",$existing_perms['data_or_ids_of_adminbyuser']);
?>
    </div><!-- end admin by user (if) -->
  </div> <!-- end access to admin (if) -->
  <br/>
<?php
    }
} else
{
?>
  <div id="access_to_admin">
    <div class="hh2">Who can manage the sheet</div>
    <div id="admin_by_user">
     <div class="hh3">These people</div>
     <div class="sus_very_small">seperate usernames by white space and/or commas</div>
     <textarea id="admin_by_user_list" class="permission_user_list" permtype="adminbyuser"><?php
if (isset($existing_perms['data_or_ids_of_adminbyuser']))
{
  sort($existing_perms['data_or_ids_of_adminbyuser']);
  echo implode(", ",$existing_perms['data_or_ids_of_adminbyuser']);
}
?></textarea>
    </div><!-- end admin by users (else) -->
  </div><!-- end access to admin (else) -->
<?php
}
?>

 </div><!-- end tabbertab for sheet permissions-->

<script type="text/javascript">
  $(document).ready(function()
  {

    $(".edit_opening_link").click(function(evt)
    {
      //alert("clicked edit opening");
      var opg = $(evt.target).attr("for_opening");
      var opgday = $(evt.target).attr("for_day");
      var theaction = "editopening";
      if ($(evt.target).attr('action') == 'addsomeone')
      {
          theaction = "addsomeone";
      }

      window.open("<?php echo $edit_opening_url; ?>&opening="+opg+"&day="+opgday+"&action="+theaction, 
                  "", 
                  "fullscreen=no,toolbar=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,directories=yes,location=no,width=800,height=700,left=100,top=100");

    });



    // code for removing an opening in the mouseover 
    $(".remove_opening_link").click(function(evt)
    {
      var conf = confirm("Really remove this opening?");
      if (conf) { handleDeleteOpeningClick(evt); }
    });
    function handleDeleteOpeningClick(evt)
    {
      $("#sus_user_notify").stop(true,true); 
      var opg = $(evt.target).attr("for_opening");
      var opgday = $(evt.target).attr("for_day");

      // need to distinguish if opening removal is coming from cal or list!
      $.ajax({
        url: 'sheet_edit_ajax.php',
        cache: false,
        data: {contextid: <?php echo $context->id;?>,
               sheet: <?php echo $sheet_id;?>,
               opening: opg,
               action: "removeopening",
               actionsource: "sheet_edit"},
        error:  function(theRequest, textStatus, errorThrown){
          notifyUser("SAVE FAILED!<br/>error connecting to the server", false, $('#day_' + opgday), 50, -60);
        },
        success:  function(data, textStatus){
          $("#openings_on_"+opgday).css("left","-999px"); // hide the summary box
          if (data.match(/^SUCCESS/)){
            notifyUser("Opening removed", true, $('#day_' + opgday), 50, -60);
  	    $(".opening_"+opg).remove();
          } else {
            notifyUser("REMOVE ABORTED!<br/>"+data, false, $('#day_' + opgday), 50, -60);
          }
         }
      });
      // consume the event here so it doesn't prop on to the mini display
      evt.stopPropagation();
    }


        prior_privacy = <?php echo $sheet->s_flag_private_signups; ?>;

        $(".signup_privacy_control").click(function(event){
          $("#sus_user_notify").stop(true,true);
          $.ajax({
            url: 'sheet_signup_privacy_ajax.php',
            //url: 'blahblah.php',
            cache: false,
            data: {contextid: <?php echo $context->id;?>,
                   sheet: <?php echo $sheet_id;?>,
                   action: (event.target.value==1?"private_signups":"public_signups")},
            error:  function(theRequest, textStatus, errorThrown)
            {
              $("#"+event.target.id).attr('checked',false);
              $("#signup_privacy_"+prior_privacy).attr('checked',true);
              notifyUser("SAVE FAILED!<br/>error connecting to the server", false, '#access_to_signup');
            },
            success:  function(data, textStatus)
            {
              if (data=='SUCCESS')
              {
                prior_privacy = event.target.value;
                notifyUser("change saved", true, '#access_to_signup');
              } else
              {
                // TODO: restore prior value
                $("#"+event.target.id).attr('checked',false);
                $("#signup_privacy_"+prior_privacy).attr('checked',true);
                notifyUser("SAVE FAILED!<br/>"+data, false, '#access_to_signup');
              }
             }
          });
        });


        $(".permission_checkbox").change(function(event){
          // do post here using $.ajax and getting relevant params from the event
          $("#sus_user_notify").stop(true,true);
          $.ajax({
            //url: "foobarbaz.php", // testing failure to connect
            url: "sheet_permissions_ajax.php", // real ajax processor
            cache: false,
            data: {contextid: <?php echo $context->id;?>,
                   sheet: <?php echo $sheet_id;?>,
                   action: (event.target.checked?"add":"remove"), 
                   permtype: $("#"+event.target.id).attr("permtype"),
                   permconstraintid: $("#"+event.target.id).attr("permval"),
                   permconstraintdata: $("#"+event.target.id).attr("permval"),
                   accessid: ($("#"+event.target.id).attr("permid")?$("#"+event.target.id).attr("permid"):0)}, // only available for pre-checked (i.e. existing perms)
            error: function(theRequest, textStatus, errorThrown)
            {
              // connection to PHP failed
              // notify user that the request failed, then inspec the XMLHttpRequest to determine what to un-check and alert user about failure
              event.target.checked = !event.target.checked;
              notifyUser("SAVE FAILED!<br/>error connecting to the server", false, '#access_to_signup');
            },
            success: function(data, textStatus)
            {
              // connection to PHP was successful
              // inspect the data to determine whether actual update was successful
              // if so, update status to notify user the setting was changed
              // ELSE, inspect data to determine what to un-check and alert user about failure
              if (data=='SUCCESS')
              {
                notifyUser("change saved", true, '#access_to_signup');
              } else 
              {
                event.target.checked = !event.target.checked;
                //$("#access_by_user_list").attr("value",data); // DEBUGGING
                notifyUser("SAVE FAILED!<br/>"+data, false, '#access_to_signup');
              }
            }
          });
         });

          //var ORIG_access_by_user_list_value = $("#access_by_user_list").attr("value");
          //var ORIG_admin_by_user_list_value = $("#admin_by_user_list").attr("value");

          var ORIG_user_lists = new Array();
          ORIG_user_lists["access_by_user_list"] = $("#access_by_user_list").attr("value");
          ORIG_user_lists["admin_by_user_list"]  = $("#admin_by_user_list").attr("value");

          //$("#access_by_user_list").blur(function(event){
          $(".permission_user_list").blur(function(event){
            if ($("#"+event.target.id).attr("value") != ORIG_user_lists[event.target.id])
            {
              $("#sus_user_notify").stop(true,true);
              $.ajax({
                 url: "sheet_permissions_ajax.php", // real ajax processor
                 cache: false,
                 data: {contextid: <?php echo $context->id;?>,
                     sheet: <?php echo $sheet_id;?>,
                     action: "add",
                     permtype: $("#"+event.target.id).attr("permtype"),
                     permconstraintid: 0,
                     permconstraintdata: $("#"+event.target.id).attr("value"),
                     //permtype: "byuser",
                     //permconstraintid: 0,
                     //permconstraintdata: $("#access_by_user_list").attr("value"),
                     accessid: 0},
                 error: function(theRequest, textStatus, errorThrown)
                {
                  $("#"+event.target.id).attr("value",ORIG_user_lists[event.target.id]);
                  notifyUser("SAVE FAILED!<br/>error connecting to the server", false, '#access_to_signup');
                },
                success: function(data, textStatus)
                {
                  if (data=='SUCCESS')
                  {
                    ORIG_user_lists[event.target.id] = $("#"+event.target.id).attr("value");
                    notifyUser("change saved", true, '#access_to_signup');
                  } else
                  {
                    $("#"+event.target.id).attr("value",ORIG_user_lists[event.target.id]);
                    notifyUser("SAVE FAILED!<br/>"+data, false, '#access_to_signup');
                  }
                }
              });
            }
          });
      }); // end document ready

    </script>


<?php
} // endif ($sheet_id != 'new')
?>

</div><!-- end tabber -->

</div><!-- end sus_sheet_data_left -->

<div class="sus_sheet_data_right">

<div class="tabber">
 <div class="tabbertab" title="Openings as Calendar">


<?php 

// get first of the month of the starting date - this is the cal start date
// get the last of the month of the ending date - this is the cal end date

include_once 'cal_lib.php';

// get the list of openings for this sheet
//$openings = getOpenings($sheet_id);

$add_opening_text = '+';

getCalendar($sheet,$create_opening_url,$add_opening_text,true);


?>
  <br style="clear:both">
  
 </div><!-- end tabbertab -->
 <div class="tabbertab" title="Openings as List">
  <div class="opening_list_format">
<?php 
if (isset($sheet->openings) && $sheet->openings[0]) // new sheets have no openings...
{
  getOpeningsList($sheet->openings,ymd($sheet->s_date_opens),$create_opening_url,$add_opening_text,true,$sheet->s_flag_private_signups);
} else
{
    echo "There are not yet openings on this sheet";
}
?>
  </div><!-- end opening_list_format -->
 </div><!-- end tabbertab -->
</div> <!-- end tabber -->

</div><!-- end sus_sheet_data_right -->

</div><!-- end sus_sheet_data -->

<script type="text/javascript">
$(document).ready(function()
{
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

<?php
if ($opening_id)
{
?>
  $(".openings_summary_box .edit_opening_link[for_opening='<?php echo $opening_id; ?>'][src*='edit.gif']").click();

<?php
}
?>

});

</script>