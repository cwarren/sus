<?php

// this script processes a signup addition or removal request. If the
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

if ($DEBUG > 0)
{
   echo 'FAILURE: ';
}

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

log_debug_r(4,$_REQUEST);

$contextid      = clean_param($_REQUEST['contextid'],PARAM_INT);                // determines what course
$access_id      = clean_param($_REQUEST['access'],PARAM_CLEAN);
$sheet_id       = clean_param($_REQUEST['sheet'],PARAM_CLEAN);
$opening_id     = clean_param($_REQUEST['opening'],PARAM_CLEAN);
$action         = clean_param($_REQUEST['action'],PARAM_CLEAN);
$actionsource   = clean_param($_REQUEST['actionsource'],PARAM_CLEAN);
$username       = clean_param($_REQUEST['username'],PARAM_CLEAN);
$admincomment   = clean_param($_REQUEST['admincomment'],PARAM_CLEAN);
$signup_id      = clean_param($_REQUEST['signup'],PARAM_CLEAN);

log_debug(1,"
contextid is $contextid
access_id is $access_id
sheet_id is $sheet_id
opening_id is $opening_id
action is $action
actionsource is $actionsource
username is $username
signup_id is $signup_id
");

/////////////////////////////////////////////////////////////////////////////////////////
// processing

$user_is_sheet_admin = userHasAdminAccess($USER->id,$sheet_id);

if ($action == 'addsignup')
{
    $user_has_signup_access = userHasSignupAccess($USER->id,$sheet_id,$access_id);
    if ((! $user_has_signup_access) && (! $user_is_sheet_admin) )
    {
      echo "FAILURE: you do not have signup access to this sheet and you are not a sheet admin";
      exit;
    }

    $for_user = $USER->id;
    if ($username)
    {
        if (! $user_is_sheet_admin)
        {
            echo "FAILURE: you are not a sheet admin and so cannot sign up other users";
            exit;
        }
        $for_user = $username;
    }
    $su = newSignup($opening_id,$for_user);
    if (! $su)
    {
        echo "FAILURE: could not create signup for user $for_user";
        exit;
    }

    // add signup
    $signup = newSignup($opening_id,$for_user);
    if ($admincomment)
    {
        $signup->admin_comment = $admincomment;
    }
    $ns_id = addSignup($signup);

    // if user is not admin
    //   get info for that opening
    //   check that max sign-ups is not exceeded
    //   if it is
    //     remove the just added signup and alert user
    if (! $user_is_sheet_admin)
    {
        $op = getOpenings($sheet_id,$opening_id);
        if ($op->o_num_signups > $op->o_max_signups)
        {
            removeSignup($ns_id);
            echo "FAILURE: opening is fully-booked";
            exit;
        }
    }    

    $sheet_complex = getStructuredSheetData($sheet_id,0,$opening_id);
    $su_user_data = $sheet_complex->openings[0]->signups_by_user[$signup->signup_user_id]->user;

    log_debug_r(2,$su_user_data);

    $confirmation_message = "
You are signed up for {$sheet_complex->s_name}.\n\n  ".ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-');

    $admin_message = "{$su_user_data->usr_firstname} {$su_user_data->usr_lastname} has signed up for the ".
                   ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-')." opening on ".$sheet_complex->s_name.".";

    if ($sheet_complex->openings[0]->o_name)
    {
        $confirmation_message .= "\n  ".$sheet_complex->openings[0]->o_name;
        $admin_message .= "\n  ".$sheet_complex->openings[0]->o_name;
    }
    if ($sheet_complex->openings[0]->o_description)
    {
        $confirmation_message .= "\n     ".$sheet_complex->openings[0]->o_description;
        $admin_message .= "\n     ".$sheet_complex->openings[0]->o_description;
    }
    if ($sheet_complex->openings[0]->o_location)
    {
        $confirmation_message .= "\n\nYou're meeting at ".$sheet_complex->openings[0]->o_location;
        $admin_message .= "\n\nThe meeting is at ".$sheet_complex->openings[0]->o_location;
    }

    $mail_error =  mailAlerts($sheet_complex,$signup->signup_user_id,
                              "Glow SUS- {$sheet_complex->s_name} at ".ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-'),
                              $confirmation_message,
                              "Glow SUS- {$su_user_data->usr_firstname} {$su_user_data->usr_lastname} signed up for ".$sheet_complex->s_name,
                              $admin_message);

    if (! $mail_error)
    {
        include_once 'cal_lib.php';  // for openingDisplay below

        $new_opening_html = openingDisplay($sheet_complex->openings[0],
                                           ($user_is_sheet_admin && ($actionsource != 'do_signup')),
                                           $sheet_complex->s_flag_private_signups);

        echo 'SUCCESS';
        if ($actionsource == 'sheet_edit_opening_form')
        {
            $sep = '___|||---';
            echo $ns_id . $sep;
            echo $sheet_complex->openings[0]->signups_by_id[$ns_id]->user->usr_firstname . $sep 
                 . $sheet_complex->openings[0]->signups_by_id[$ns_id]->user->usr_lastname . $sep;
            echo $admincomment . $sep . mktime();
        } else
        {
            echo $new_opening_html;
        }
    }
    exit;

} else if ($action == 'removesignup')
{
    if (! $signup_id)
        {
        echo 'FAILURE: no signup specified';
        exit;
    }

    $sheet_complex = getStructuredSheetData($sheet_id,0,$opening_id,$signup_id);
    $su = $sheet_complex->openings[0]->signups_by_id[$signup_id];
    $su_user_data = $su->user;

    // log_debug_r(-1,$su);

    // user either has to own the given signup (i.e. be the one signed up) or be an admin of the sheet for that signup
    $removal_success = 0;
    if ($user_is_sheet_admin)
    {
        $removal_success = removeSignup($signup_id);
    } else
    {
        if ($su->su_signup_user_id == $USER->id)
        {
            $removal_success = removeSignup($signup_id);
        }
    }
    if ($removal_success)
    {

        $confirmation_message = "\nSignup cancelled for {$sheet_complex->s_name}.\n\n  ".ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-');

        $admin_message = "Cancelled {$su_user_data->usr_firstname} {$su_user_data->usr_lastname} signup for the ".
                       ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-')." opening on ".$sheet_complex->s_name.".\n";

        if ($sheet_complex->openings[0]->o_name)
        {
            $confirmation_message .= "\n  ".$sheet_complex->openings[0]->o_name;
            $admin_message .= "\n  ".$sheet_complex->openings[0]->o_name;
        }
        if ($sheet_complex->openings[0]->o_description)
        {
            $confirmation_message .= "\n     ".$sheet_complex->openings[0]->o_description;
            $admin_message .= "\n     ".$sheet_complex->openings[0]->o_description;
        }

        $mail_error =  mailAlerts($sheet_complex,$su->su_signup_user_id,
                                  "Glow SUS- signup cancelled on {$sheet_complex->s_name} at ".ymd_hm_a($sheet_complex->openings[0]->o_begin_datetime,'-'),
                                  $confirmation_message,
                                  "Glow SUS- cancelled {$su_user_data->usr_firstname} {$su_user_data->usr_lastname} signup for ".$sheet_complex->s_name,
                                  $admin_message);

        include_once 'cal_lib.php'; // for openingDisplay below
        $sheet_complex = getStructuredSheetData($sheet_id,0,$opening_id);
        log_debug_r(4,$sheet_complex);

        $new_opening_html = openingDisplay($sheet_complex->openings[0],($user_is_sheet_admin && ($actionsource != 'do_signup')),$sheet_complex->s_flag_private_signups);

        echo 'SUCCESS';
        echo $new_opening_html;
        exit;
    } else
    {
        echo 'FAILURE: could not remove';
        exit;
    }
    
} else 
{
    echo 'FAILURE: unknown action $action';
    exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////

# takes: 
#   a sheet complex
#   subject for signee alert
#   body for signee alert
#   subject for admin alert
#   body for admin alert
# does: send alert email to the signee and admin as appropriate
# returns: true on all success, false on 1 or more failures
function mailAlerts($sheet_complex,$signup_user_id,$sign_subj,$sign_body,$admin_subj,$admin_body)
{
    $mail_error = false;

    $su_user_data = $sheet_complex->openings[0]->signups_by_user[$signup_user_id]->user;

    // log_debug_r(-2,$su_user_data);


    if (! simpleEmail($su_user_data->usr_email, $sign_subj, $sign_body)) {
        echo 'FAILURE: signup handled, but no confirmation email sent';
        $mail_error = true;
    }

    if ($sheet_complex->s_flag_alert_owner_signup || $sheet_complex->s_flag_alert_admin_signup)
    {

        $users_to_alert = getSheetOwnerAndAdmins($sheet_complex->s_id);
        $owner = $users_to_alert[0];

        // trim off owner xor admins as necessary
        if (! $sheet_complex->s_flag_alert_owner_signup)
        {
          array_shift($users_to_alert);
        } else if (! $sheet_complex->s_flag_alert_admin_signup)
        {
          $users_to_alert = array(array_shift($users_to_alert));
        }

        // CSW 200/02/24 : modify owner info to make mail message clearer
        $owner->firstname = 'Glow SUS';
        $owner->lastname = 'Admin';
        // NOTE: ideally the message would be from a noreply address,
        // any change to the owner email address causes a silent and
        // inexplicable mail deliver failure

        $mailresult = true;
        foreach ($users_to_alert as $u)
        {
            $mailresult = $mailresult && email_to_user($u, $owner, $admin_subj, $admin_body);
        }
        
        if (! $mailresult)
        {
            echo 'FAILURE: signup handled, but admin alerts failed';
            $mail_error = true;
        }
    }

   return $mail_error;
}


?>