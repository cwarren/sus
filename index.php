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

include_once 'sus_lib.php';

/////////////////////////////////////////////////////////////////////////////////////////
// input validation

$contextid    = optional_param('contextid', 0, PARAM_INT);                // determines what course
$action       = optional_param('action', 'managesheets');
$btn_cancel   = optional_param('action_cancel','');
$btn_save     = optional_param('action_save','');
$context = '';
$course  = '';

// this ensures the course and context objects are set correctly, based on the provided contextid
if ($contextid) {
    if (! $context = get_context_instance_by_id($contextid)) {
        error("Context ID is incorrect");
    }
    // not needed anymore
    unset($contextid);

    if (! $course = get_record('course', 'id', $context->instanceid)) {
        error("Course ID is incorrect");
    }
} else {
    // how do other blocks handle a lack of context? They can't all just fail when not in a course.... right....?
    error("contextid of '$contextid' is not valid");
    // CSW TODO: handle correctly when context is either front page (actually, I think that counts as a 'course'), or mymoodle (perhaps also a 'course'?)
    // CSW 2009/06/03 - front page and my moodle do both have a context, BUT it is the same thing
}

/////////////////////////////////////////////////////////////////////////////////////////
// access / permissions checking

//ORIG: require_login($course); // CSW TODO: what does this do, exactly? 
// 2009/06/03 - it makes sure the user is logged in and has access to the given course. If the course ID is left out, just makes sure the user is logged in
require_login();

$sitecontext = get_context_instance(CONTEXT_SYSTEM);
$frontpagectx = get_context_instance(CONTEXT_COURSE, SITEID);

if ( ($context->id == $frontpagectx->id) || ($context->id == $sitecontext) ) {

    // CSW CHECK: do we actually care what course the user is in for
    // this? I don't think so... the block is really just a gateway into
    // the sign-up sheet system. We may use the current course as a
    // default for signee access, but perhaps not even that...
    // CSW 2009/06/03 - we do in fact care about it, since we need to know whether to show course sheets where applicable
}

// CSW CHECK: I don't think we care about roles at all for this....?
// The sus system basically has it's own roles (owner, admin, and other/signee)...

// modify the $USER object to include info about the user's signups
setUserSignupInfo();

///////////////////////////////////////////////////////////////////////////////////////
// page headers

$blocktitle = get_string('blocktitle', 'block_signup_sheets');

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = $CFG->wwwroot.'/block/signup_sheets/?contextid='.$context->id;

$navlinks = array();
if (! ( ($context->id == $frontpagectx->id) || ($context->id == $sitecontext) )) {
    $navlinks[] = array('name' => $course->shortname,
                        'link' => "$CFG->wwwroot/course/view.php?id=$course->id",
                        'type' => 'misc');
}
$navlinks[] = array('name' => $blocktitle, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);

// CSW NOTE: this will need to be mucked with, I think, since we're not always in a course....?
print_header("$course->shortname: ".$blocktitle, $course->fullname, $navigation, "", "", true, "&nbsp;", navmenu($course));

echo "<!-- start sus custom output -->\n";

echo '<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/signup_sheets/js/jquery/jquery.js"></script>';
echo '<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/signup_sheets/js/jquery/ui/jquery-ui.js"></script>';
echo '<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/signup_sheets/js/sus_lib.js"></script>';
echo '<div class="sus_content">'."\n";

////////////////////////////
// internal (to block) nav

$ss_href  = $CFG->wwwroot.'/blocks/signup_sheets/?contextid='.$context->id;
$edit_group_url = "$ss_href&action=editgroup&sheetgroup=";
$add_group_url  = "$ss_href&action=editgroup&sheetgroup=new";
$edit_sheet_url = "$ss_href&action=editsheet&sheet=";
$add_sheet_url  = "$ss_href&action=editsheet&sheet=new&sheetgroup=";

//echo "ss_href is $ss_href<br/>\n";

$actionsignupclass = '';
$actionsignupsclass = '';
$actionmanagesheetsclass = '';
if ($action == 'managesheets' || $action == 'editgroup' || $action == 'newopening' ||
    $action == 'editsheet' || $action == 'deletegroup' || $action == 'deletesheet') {
    $actionmanagesheetsclass = ' class="curaction"';
} else if ($action == 'listsignups') {
    $actionsignupsclass = ' class="curaction"';
} else if ($action == 'signup' || $action == 'signuponsheet') {
    $actionsignupclass = ' class="curaction"';
} else if ($action == 'help') {
    $actionhelpclass = ' class="curaction"';
}

$ssnav = array();
$ssnav[] = "<a href=\"$ss_href&action=signup\"$actionsignupclass>".get_string('nav_signup', 'block_signup_sheets').'</a>';
$ssnav[] = "<a href=\"$ss_href&action=listsignups\"$actionsignupsclass>".get_string('nav_mysignups', 'block_signup_sheets').'</a>';
$ssnav[] = "<a href=\"$ss_href&action=managesheets\"$actionmanagesheetsclass>".get_string('nav_mysheets', 'block_signup_sheets').'</a>';
$ssnav[] = "<a href=\"$ss_href&action=help\"$actionhelpclass>".get_string('nav_help', 'block_signup_sheets').'</a>';

echo '<div class="sus_nav">'."\n";
echo "  <ul>\n";
foreach ($ssnav as $ssnavitem) {
    echo "    <li>$ssnavitem</li>\n";
}
echo "  </ul>\n";
echo "</div><br clear=\"all\"/>\n";

///////////////////////////////////////////////////////////////////////////////////////
// main functionality

//echo "<br/>\n"; // CSW DEBUG
//echo "action.btn_cancel is $action$btn_cancel<br/>\n"; // CSW DEBUG

$action_table = array(
   'managesheets' => 'manage_sheets.php'
  ,'listsignups' => 'my_signups_list.php'
  ,'signup' => 'do_signup.php'
  ,'signuponsheet' => 'do_signup_on_sheet.php'
  ,'editgroup' => 'group_edit.php'
  ,'editgroupCancel' => 'manage_sheets.php'
  ,'deletegroup' => 'manage_sheets.php'
  ,'editsheet' => 'sheet_edit.php'
  ,'editsheetCancel' => 'manage_sheets.php'
  ,'deletesheet' => 'manage_sheets.php'
  ,'help' => 'help.php'
);

if (in_array($action.$btn_cancel,array_keys($action_table)))
{
    include $action_table[$action.$btn_cancel];
} else
{
  echo "Sorry, you've attempted an action ('$action$btn_cancel') that I don't know how to handle.";
}

///////////////////////////////////////////////////////////////////////////////////////
// page footers

echo "</div>";
//echo "<!--\n";
//print_r($course);
//echo "-->\n";
echo "<!-- end sus_content -->\n";

// CSW NOTE: this will need to be mucked with, I think, since we're not always in a course....?
print_footer($course);
?>