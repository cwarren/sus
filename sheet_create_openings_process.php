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
$day            = clean_param($_REQUEST['day'],PARAM_CLEAN); 
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);

$name          = clean_param($_REQUEST['opening_name'],PARAM_CLEAN);
$description   = clean_param($_REQUEST['opening_description'],PARAM_CLEAN);
$admin_comment = clean_param($_REQUEST['opening_admin_comment'],PARAM_CLEAN);
$openingLocation = clean_param($_REQUEST['opening_location'],PARAM_CLEAN);

$day_y = substr($day,0,4);
$day_m = substr($day,4,2);
$day_d = substr($day,6,2);

debug(1,"day (y,m,d) = $day ($day_y,$day_m,$day_d)");

$begintime_hour = clean_param($_REQUEST['begintime_hour'],PARAM_INT);
if ($begintime_hour==12)
{
    $begintime_hour=0;
}
$begintime_minute = clean_param($_REQUEST['begintime_minute'],PARAM_INT);
$begintime_ampm = clean_param($_REQUEST['begintime_ampm'],PARAM_CLEAN);
if (($begintime_ampm == 'pm') && ($begintime_hour != 12))
{
    $begintime_hour += 12;
}

$opening_spec_type = clean_param($_REQUEST['opening_spec_type'],PARAM_CLEAN);
debug(3,"opening_spec_type is $opening_spec_type");

$endtime_hour = clean_param($_REQUEST['endtime_hour'],PARAM_INT);
$endtime_minute = clean_param($_REQUEST['endtime_minute'],PARAM_INT);
$endtime_ampm = clean_param($_REQUEST['endtime_ampm'],PARAM_CLEAN);
if (($endtime_ampm == 'pm') && ($endtime_hour != 12))
{
    $endtime_hour += 12;
}

$duration_per_opening = clean_param($_REQUEST['durationEachOpening'],PARAM_INT);
//debug(3," is $");
debug(3,"duration_per_opening is $duration_per_opening");

$numOpeningsInTimeRange = clean_param($_REQUEST['numOpeningsInTimeRange'],PARAM_CLEAN);
$numSignupsPerOpening = clean_param($_REQUEST['numSignupsPerOpening'],PARAM_CLEAN);

$openingRepeatRate = clean_param($_REQUEST['openingRepeatRate'],PARAM_CLEAN);
$dow_tags = array('sun','mon','tue','wed','thu','fri','sat');
$repeat_dow = array();
foreach ($dow_tags as $dow)
{
    $repeat_dow[$dow] = clean_param($_REQUEST['repeat_dow_'.$dow],PARAM_INT);
}
$repeat_dom = array();
for ($dom = 1; $dom <= 31; $dom++)
{
    $repeat_dom[$dom] =  clean_param($_REQUEST['repeat_dom_'.$dom],PARAM_INT); // 1 through 31
}

$until_date = clean_param($_REQUEST['until_date'], PARAM_CLEAN);
if ($openingRepeatRate == 1)
{
  $until_date = "$day_y-$day_m-$day_d";
  $until_y = $day_y;
  $until_m = $day_m;
  $until_d = $day_d;
} else
{
  $until_parts = explode('-',$until_date);
  $until_y = $until_parts[0];
  $until_m = $until_parts[1];
  $until_d = $until_parts[2];
}

debug(1,"until_date (y,m,d) = $until_date ($until_y,$until_m,$until_d)");


/////////////////////////////////////////////////////////////////////////////////////////
// processing


// opening_set_id is current datetime concat-ed with the current user id
$opening_set_id = time() . $USER->id;

// figure out the start and end times
$timestamp_begin = mktime($begintime_hour,$begintime_minute,10,$day_m,$day_d,$day_y);
$timestamp_end = mktime($endtime_hour,$endtime_minute,10,$day_m,$day_d,$day_y);
// then divide their difference by the number of openings to the the length of each opening (i.e. the openingTimeSpan)
$openingTimeSpan = ($timestamp_end - $timestamp_begin) / $numOpeningsInTimeRange;
if ($opening_spec_type == "by_duration")
{
    $openingTimeSpan = $duration_per_opening * 60;
}

// for start date to until date, by day
//  if (($openingRepeatRate == 1) || (($openingRepeatRate == 2) && ($repeat_dow[curDow] == 1)) || (($openingRepeatRate == 3) && ($repeat_dom[$curDom]==1))
//   starting_at = init start time
//   for 1 to number of openings
//    ending_at = starting_at + openingTimeSpan
//    create an opening starting at cur day starting_at and ending at cur day ending_at
//    starting_at = ending_at
//   end for
//  end if
// end for

$loop_day = mktime(10,10,10,$day_m,$day_d,$day_y);
$end_day =  mktime(11,10,10,$until_m,$until_d,$until_y);
debug(2,"$loop_day is loop_day");
debug(2,"$end_day is end_day");
debug(2,"openingRepeatRate is $openingRepeatRate");
$affectedDays = array();
$add_opening_problems = array();
while ($loop_day <= $end_day)
{
    debug(2,"cur loop_day is $loop_day");
    debug(2,"cur loop_day is ".date('Y',$loop_day).date('m',$loop_day).date('d',$loop_day));
    debug(2,"cur end_day is $end_day");

    $curDow = strtolower(date('D',$loop_day));
    $curDom = date('j',$loop_day);

    debug(3,"repeat_dow[$curDow] is ".$repeat_dow[$curDow]);
    debug(3,"repeat_dom[$curDom] is ".$repeat_dom[$curDom]);
    if (($openingRepeatRate == 1) 
        || (($openingRepeatRate == 2) && ($repeat_dow[$curDow]==1)) 
        || (($openingRepeatRate == 3) && ($repeat_dom[$curDom]==1))
       ) 
    {
        debug(3,"adding openings");
        $openingRangeBegin = mktime(date('G',$timestamp_begin), date('i',$timestamp_begin)*1,0,date('m',$loop_day),date('d',$loop_day),date('Y',$loop_day));
        //$openingRangeEnd = mktime(date('G',$timestamp_end), date('i',$timestamp_end)*1,0,date('m',$loop_day),date('d',$loop_day),date('Y',$loop_day));
        $curOpeningBegin = $openingRangeBegin;
        $curOpeningEnd = $curOpeningBegin + $openingTimeSpan;
        $numOpeningsCreated = 0;
        while ($numOpeningsCreated < $numOpeningsInTimeRange)
        {
            $opening = newOpening($sheet_id);
            $opening->opening_set_id = $opening_set_id;
            debug(4,"opening_set_id is $opening_set_id");
            debug(5,"opening->opening_set_id is $opening->opening_set_id");
            $opening->begin_datetime = $curOpeningBegin;
            $opening->end_datetime = $curOpeningEnd;
            debug(4,"begin is ".date('Y',$opening->begin_datetime).date('m',$opening->begin_datetime).date('d',$opening->begin_datetime)." ".date('G',$opening->begin_datetime).':'.date('i',$opening->begin_datetime));
            debug(4,"end is ".date('Y',$opening->end_datetime).date('m',$opening->end_datetime).date('d',$opening->end_datetime)." ".date('G',$opening->end_datetime).':'.date('i',$opening->end_datetime));


            $opening->max_signups = $numSignupsPerOpening;
            $opening->location = $openingLocation;
	    $opening->name = $name;
	    $opening->description = $description;
            $opening->admin_comment = $admin_comment;

            //date('',$opening->begin_datetime)

	    $add_result = addOpening($opening);

            if ($add_result == 0)
            {
                $add_opening_problems[] = ymd($opening->begin_datetime,'-')." ".hmi_a($opening->begin_datetime)." to ".him_a($opening->end_datetime)." :: ".mysql_error(); 
		debug(1,"adding opening general failure on:");
                debug_r(1,$opening);
            }
            if ($add_result == -1)
            {
                $add_opening_problems[] = ymd($opening->begin_datetime,'-')." ".hmi_a($opening->begin_datetime)." to ".hmi_a($opening->end_datetime)." :: conflicts with another opening on this sheet"; 
		debug(1,"adding opening conflict failure on:");
                debug_r(1,$opening);
            }
            debug_r(2,$opening);

            // prep next loop pass
            $curOpeningBegin = $curOpeningEnd;
            $curOpeningEnd = $curOpeningBegin + $openingTimeSpan;
            $numOpeningsCreated++;
        }

    //debug(2,"again, cur loop_day is ".date('Y',$loop_day).date('m',$loop_day).date('d',$loop_day));
        
        $affectedDays[] = date('Y',$loop_day).date('m',$loop_day).date('d',$loop_day);
        debug(3,"last affectedDays is ". $affectedDays[count($affectedDays)-1]);
    }

    //$next_day = $loop_day + 87400;
    $loop_day = dayAfter_timeAsDate($loop_day);
    //$loop_day = mktime(10,10,10,date('Y',$next_day),date('m',$next_day),date('d',$next_day));
}

//exit; // DEBUG

?>
<script type="text/javascript"> 
<?php 
?>
<?php 
?>
window.opener.document.getElementById("save_sheet_button").click();
<?php
 if (($DEBUG < 1) // don't close the window when debugging
     && (count($add_opening_problems) < 1))     // don't close if errors
 {
    echo "window.close();";
 }
?>
</script>
<?php
if (count($add_opening_problems) > 0)
{
    print_header_simple("Signup Sheets - Add Openings Problems", "", '', "", "", false, "&nbsp;", '');
    echo "<h1>Problems adding openings:</h1>\n";
    echo "<ul>\n";
    foreach ($add_opening_problems as $problem)
    {
        echo "  <li>$problem</li>\n";
    }
    echo "</ul>\n";
    echo "<h2><a href=\"#\" onclick=\"window.close();\">Close this window</a></h2>";
}
?>