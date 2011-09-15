<?php

// this script processes an opening removal request. If the
// process fails for some reason it responds with "FAILURE: reason". On
// success it responds with "SUCCESS"

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

$contextid      = clean_param($_REQUEST['contextid'],PARAM_INT);                // determines what course
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id     = clean_param($_REQUEST['opening'],PARAM_CLEAN);
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);
$actionsource   = clean_param($_REQUEST['actionsource'],PARAM_CLEAN);

log_debug(1,"
contextid is $contextid
sheet_id is $sheet_id
opening_id is $opening_id
action is $action
actionsource is $actionsource
");

/////////////////////////////////////////////////////////////////////////////////////////
// processing


$user_is_sheet_admin = userHasAdminAccess($USER->id,$sheet_id);

if (! $user_is_sheet_admin) {
  echo "FAILURE: you are not a sheet admin";
  exit;
}

if ($action == 'removeopening'){
    if (! $opening_id)
        {
        echo 'FAILURE: no opening specified';
        exit;
    }

    // user either has to be an admin of the sheet for that signup
    $removal_success = 0;
    if ($user_is_sheet_admin){
        $removal_success = removeOpening($opening_id);
    } 
    if ($removal_success){
        echo 'SUCCESS';
        exit;
    } else {
        echo 'FAILURE: could not remove opening $opening_id';
        exit;
    }
    
} else 
{
    echo 'FAILURE: unknown action $action';
    exit;
}

?>