<?php

// this script processes a send email to all signees of an opening. If the
// process fails for some reason it responds with "FAILURE: reason". On
// success it responds with "SUCCESS<data>" where <data> is the updated
// HTML for the opening to which the signup was attached. 

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
$sheet_id        = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id      = clean_param($_REQUEST['opening'],PARAM_CLEAN);
$action          = clean_param($_REQUEST['action'],PARAM_CLEAN);
$actionsource    = clean_param($_REQUEST['actionsource'],PARAM_CLEAN);
$email_subject   = clean_param($_REQUEST['email_subject'],PARAM_CLEAN);
$email_body      = clean_param($_REQUEST['email_body'],PARAM_CLEAN);

log_debug(1,"
contextid is $contextid
sheet_id is $sheet_id
opening_id is $opening_id
action is $action
actionsource is $actionsource
email_subject is $email_subject
email_body is $email_body
");

/////////////////////////////////////////////////////////////////////////////////////////
// processing

$user_is_sheet_admin = userHasAdminAccess($USER->id,$sheet_id);

if ($action == 'sendemail')
{
    if (! $user_is_sheet_admin)
    {
      echo "FAILURE: you are not a sheet admin";
      exit;
    }

    $sheet_complex = getStructuredSheetData($sheet_id,0,$opening_id);

    
    $to_ar = array($USER->email);
    $cc_ar = array();
    $bcc_ar = array();
    foreach ($sheet_complex->openings[0]->signups as $su)
    {
        $bcc_ar[] = $su->user->usr_email;
    }

    log_debug_r(2,$bcc_ar);

    // if (email_to_users($bcc_ar, $USER, $email_subject, $email_body))
    if (sendEmail($to_ar,$cc_ar,$bcc_ar,$USER->email,'',$email_subject,$email_body))
    {
        echo 'SUCCESS';
    } else
    {
        echo 'FAILURE: sending email failed';
    }
    exit;

} else 
{
    echo 'FAILURE: unknown action $action';
    exit;
}

/////////////////////////////////////////////////////////////////////////////////////////

?>