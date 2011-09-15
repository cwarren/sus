<?php

// this script gets the past sign-ups the current user has made and
// returns them as a formatted list (prefixed with SUCCESS). On a failure
// it returns FAILURE with a brief description appended.

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

log_debug_r(4,$_REQUEST);

$contextid       = clean_param($_REQUEST['contextid'],PARAM_INT);                // determines what course
$action          = clean_param($_REQUEST['action'],PARAM_CLEAN);
$actionsource    = clean_param($_REQUEST['actionsource'],PARAM_CLEAN);

log_debug(1,"
contextid is $contextid
action is $action
actionsource is $actionsource
");

/////////////////////////////////////////////////////////////////////////////////////////
// processing

$limit = ymd(dayBefore_timeAsDate(mktime()));

if ($action == 'fetchmypast')
{
    $my_signups = getSignupsBySignee($USER->id,'',$limit);
    echo 'SUCCESS';
    generateMySignupsList($my_signups);
    exit;

} else if ($action == 'fetchformepast')
{
    $signups_for_me = getSignupsForSheetsOf($USER->id,$USER->username,'',$limit);
    echo 'SUCCESS';
    generateSignupsForMeList($signups_for_me);
    exit;

} else 
{
    echo 'FAILURE: unknown action $action';
    exit;
}

/////////////////////////////////////////////////////////////////////////////////////////

?>