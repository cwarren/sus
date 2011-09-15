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

$DEBUG=0;

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

debug_r(5,$_REQUEST);

$contextid      = optional_param($_REQUEST['contextid'], 0, PARAM_INT);                // determines what course
$sheet_group_id = clean_param($_REQUEST['sheet_group'],PARAM_CLEAN);
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id     = clean_param($_REQUEST['opening'],PARAM_CLEAN);
$opening_set_id = clean_param($_REQUEST['opening_set'],PARAM_CLEAN);
$day            = explode('-',clean_param($_REQUEST['on_date'],PARAM_CLEAN)); 
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);


$name          = clean_param($_REQUEST['opening_name'],PARAM_CLEAN);
$description   = clean_param($_REQUEST['opening_description'],PARAM_CLEAN);
$admin_comment = clean_param($_REQUEST['opening_admin_comment'],PARAM_CLEAN);
$openingLocation = clean_param($_REQUEST['opening_location'],PARAM_CLEAN);


//$day_y = substr($day,0,4);
//$day_m = substr($day,4,2);
//$day_d = substr($day,6,2);

$day_y = $day[0];
$day_m = $day[1];
$day_d = $day[2];

debug(1,"day (y,m,d) = $day ($day_y,$day_m,$day_d)");

$begintime_hour = clean_param($_REQUEST['begintime_hour'],PARAM_INT);
$begintime_minute = clean_param($_REQUEST['begintime_minute'],PARAM_INT);
$begintime_ampm = clean_param($_REQUEST['begintime_ampm'],PARAM_CLEAN);
if ($begintime_ampm == 'pm')
{
    $begintime_hour += 12;
}

$endtime_hour = clean_param($_REQUEST['endtime_hour'],PARAM_INT);
$endtime_minute = clean_param($_REQUEST['endtime_minute'],PARAM_INT);
$endtime_ampm = clean_param($_REQUEST['endtime_ampm'],PARAM_CLEAN);
if ($endtime_ampm == 'pm')
{
    $endtime_hour += 12;
}

$numSignupsPerOpening = clean_param($_REQUEST['numSignupsPerOpening'],PARAM_CLEAN);

$su_admin_comments = array();
foreach (array_keys($_REQUEST) as $rkey)
{
    if (preg_match('/su\\_admin\\_comment\\_(\\d+)/',$rkey,$matches))
    {
        $su_admin_comments[$matches[1]] =  clean_param($_REQUEST[$rkey],PARAM_CLEAN);
    }
}

debug_r(3,$su_admin_comments);

/////////////////////////////////////////////////////////////////////////////////////////
// processing


// figure out the start and end times
$timestamp_begin = mktime($begintime_hour,$begintime_minute,10,$day_m,$day_d,$day_y);
$timestamp_end = mktime($endtime_hour,$endtime_minute,10,$day_m,$day_d,$day_y);

$opinf = array();

$opinf['name'] = $name;
$opinf['description'] = $description;
$opinf['admin_comment'] = $admin_comment;
$opinf['location'] = $openingLocation;
$opinf['opening_set_id'] = $opening_set_id;
$opinf['begin_datetime'] = $timestamp_begin;
$opinf['end_datetime'] = $timestamp_end;
$opinf['max_signups'] = $numSignupsPerOpening;

debug_r(4,$opinf);

if (! updateOpening($opening_id,$opinf))
{
    echo "updating opening failed";
    debug_r(1,$opinf);
    exit;
}

// opening done, now handle admin comments on individual signups

$su_admin_comment_errors = '';
foreach ($su_admin_comments as $su_id => $comment_text)
{
    $su_info = array('admin_comment'=>$comment_text);
    if (! updateSignup($su_id,$su_info))
    {
        $su_admin_comment_errors .= "problem updating signup $su_id<br/>\n";
    }
}
if  ($su_admin_comment_errors)
{
    echo "updating sign-ups failed:<br/>\n$su_admin_comment_errors";
    debug_r(1,$su_admin_comments);
    exit;
}


//exit; // DEBUG

?>
<script type="text/javascript"> 
//alert("processing new openings");
window.opener.document.getElementById("save_sheet_button").click();
<?php
 if ($DEBUG < 1) // don't close the window when debugging
 {
   echo "window.close();";
 }
?>
</script>