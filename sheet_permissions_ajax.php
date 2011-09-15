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
$perm_type      = clean_param($_REQUEST['permtype'],PARAM_CLEAN);
$perm_constraint_id    = clean_param($_REQUEST['permconstraintid'],PARAM_CLEAN);
$perm_constraint_data  = clean_param($_REQUEST['permconstraintdata'],PARAM_CLEAN);
$access_id       = clean_param($_REQUEST['accessid'],PARAM_CLEAN);


log_debug(1,"
contextid is $contextid
sheet_id is $sheet_id
action is $action
perm_type is $perm_type
perm_constraint_id is $perm_constraint_id
perm_constraint_data is $perm_constraint_data
access_id is $access_id
");


/////////////////////////////////////////////////////////////////////////////////////////

// takes: an access object
// does: tries to add it to the DB, prints an approp message, and exits this script
function addAccess($accessObj)
{
    if (addAccessPermission($accessObj))
    {
        echo 'SUCCESS';
    } else {
        echo 'FAILURE: access record not saved';
    }
    exit;
}


function handleByUser($sheet_id,$perm_constraint_data,$perm_type)
{
    $perm_constraint_data = preg_replace('/,|\\s+/',' ',$perm_constraint_data); // convert commas and white space to single white space
    $perm_constraint_data = preg_replace('/^\\s+|\\s+$/','',$perm_constraint_data); // trim leading and trailing space
    $usernames = explode(' ',$perm_constraint_data);
    log_debug(2,"usernames is (".implode(',',$usernames).")");
    log_debug(2,"perm_type is $perm_type");
    // get all byuser or adminbyuser access items for this sheet - $existing_by_user_access
    $existing_by_user_access = getAccessPermissions($sheet_id,$perm_type);
    //log_debug(3,"existing_by_user_access is (".implode(',',$existing_by_user_access).")"); // hmmmm - this line is causing  PHP Notice:  Array to string conversion in /opt/web/moodledev/blocks/signup_sheets/sheet_permissions_ajax.php on line 73 in the error logs - keeping it commented out for now to avoid filling the logs
    log_debug(3,"existing_by_user_access[data_or_ids_of_$perm_type] is (".implode(',',$existing_by_user_access['data_or_ids_of_'.$perm_type]).")");
    $to_add = array();
    $to_del = array();
    foreach ($usernames as $username)
    {
      if (! in_array($username,$existing_by_user_access['data_or_ids_of_'.$perm_type]))
      {
        $to_add[] = $username;
      }
    }
    foreach ($existing_by_user_access['data_or_ids_of_'.$perm_type] as $existing)
    {
      if (! in_array($existing,$usernames))
      {
        $to_del[] = $existing;
      }
    }
    $add_failure = array();
    log_debug(3,"to_add is (".implode(',',$to_add).")");
    foreach ($to_add as $add)
    {
      log_debug(4,"add is $add");
      $access = newAccess($sheet_id);
      $access->type = $perm_type;
      $access->constraint_data = $add;
      log_debug_r(4,$access);
      if (!addAccessPermission($access))
      {
        $add_failure[] = $add;
      }
    }
    $del_failure = array();
    log_debug(3,"to_del is (".implode(',',$to_del).")");
    foreach ($to_del as $del)
    {
      $access_to_del = newAccess($sheet_id);
      $access_to_del->type = $perm_type;
      $access_to_del->constraint_data = $del;
      log_debug_r(4,$access_to_del);
      if (!removeAccessPermission($access_to_del))
      {
        $del_failure[] = $del;
      }
    }
    if (! ($add_failure || $del_failure))
    {
      echo 'SUCCESS';
      exit;
    } else
    {
      if ($add_failure)
      {
        echo "FAILURE TO ADD: $add_failure\n";
      } 
      if ($del_failure) 
      {
        echo "FAILURE TO DELETE: $del_failure\n";
      }
      exit;
    }
}

/////////////////////////////////////////////////////////////////////////////////////////
// processing


if (($perm_type == 'byuser') || ($perm_type == 'adminbyuser'))
{
    handleByUser($sheet_id,$perm_constraint_data,$perm_type);
} else
{
 if ($action == 'add')
 {
  $access = newAccess($sheet_id);
  switch ($perm_type)
  {
    case 'bycourse':
      $access->type = 'bycourse';
      $access->constraint_id = $perm_constraint_id;
      addAccess($access);
      break;
    case 'byinstr':
      $access->type = 'byinstr';
      $access->constraint_id = $perm_constraint_id;
      addAccess($access);
      break;
    case 'bydept':
      $access->type = 'bydept';
      $access->constraint_data = $perm_constraint_data;
      addAccess($access);
      break;
    case 'bygradyear':
      $access->type = 'bygradyear';
      $access->constraint_data = $perm_constraint_data;
      addAccess($access);
      break;
    case 'byrole':
      $access->type = 'byrole';
      $access->constraint_data = $perm_constraint_data;
      addAccess($access);
      break;
    case 'byhasaccount':
      $access->type = 'byhasaccount';
      $access->constraint_data = 'all';
      addAccess($access);
      break;
    default:
      echo 'FAILURE: unknown access type';
      exit;
      break;
  }
 } else if ($action == 'remove')
 {
  if (removeAccessPermission($access_id))
  {
     echo 'SUCCESS';
  } else
  {
     echo "FAILURE: access not removed";
  }
  exit;
 }
}


/*
*/

print_r($_REQUEST);

?>