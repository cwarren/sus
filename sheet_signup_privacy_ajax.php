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

$DEBUG=-1;

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

debug_r(5,$_REQUEST);

$contextid      = optional_param($_REQUEST['contextid'], 0, PARAM_INT);                // determines what course
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);

log_debug(1,"
contextid is $contextid
sheet_id is $sheet_id
action is $action
");


/////////////////////////////////////////////////////////////////////////////////////////
// processing


$private_val = 1;
if ($action == 'private_signups')
{
  $private_val = 1;
} else if ($action == 'public_signups')
{
  $private_val = 0;
} else 
{
  echo 'FAILURE: unknown action';
  exit;
}

// TODO: check that current user has admin access to the sheet

$privacy = array('flag_private_signups' => $private_val);

if (updateSheet($sheet_id,$privacy))
{
  echo 'SUCCESS';
} else
{
  echo 'FAILURE: could not update DB';
}
/*
print_r($_REQUEST);
*/
?>