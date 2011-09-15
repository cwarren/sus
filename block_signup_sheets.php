<?php 
// This function returns true. It is called by various included files
// to make sure they're actually in the signup sheet world - i.e. to
// prevent them from being (usefully) used directly from the web. E.g.
// if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }
function verify_in_signup_sheets() {
    return true;
}

include 'sus_lib.php';

class block_signup_sheets extends block_base {
    function init() {
      $this->title   = get_string('blocktitle', 'block_signup_sheets');
      $this->version = 2009051900;
      $this->cron = 86401; /// Set minimum time between cron executions to 86401 secs (1 day 1 second)
    }

    function get_content() {
        global $USER, $CFG, $COURSE;

        $this->content = new object();

//        $this->content->text = 'Hello '.$USER->firstname.' '.$USER->lastname;
//        $this->content->footer = 'The End.';

        include_once 'sus_lib.php';

        // CSW NOTE- do we really need the current course context?
        // CSW- probably, so we know which sheets to display at the top 
        // get the context, or else abort
        if (!$currentcontext = get_context_instance(CONTEXT_COURSE, $COURSE->id)) {
            $this->content = '';
            return $this->content;
        }

        // hide / cut-out if not logged in
        if ($USER->id == 0)
        {
            $this->content = '';
            return $this->content;
        }
//get_string('blocktitle', 'block_signup_sheets');

        $href  = $CFG->wwwroot.'/blocks/signup_sheets/?contextid='.$currentcontext->id;

        $this->content->text = '<div class="sus_block_body">';

        $this->content->text .= '<div class="sus_tool_links">';
        $this->content->text .= '<a href="'.$href.'&action=signup">'.get_string('nav_signup', 'block_signup_sheets')."</a> | ";
        $this->content->text .= '<a href="'.$href.'&action=managesheets">'.get_string('nav_mysheets', 'block_signup_sheets')."</a>\n";
        $this->content->text .= '</div>';

        $this->content->text .= '<h4><a href="'.$href.'&action=listsignups">'.get_string('nav_mysignups', 'block_signup_sheets')."</a></h4>\n";
        $today = ymd();
        $look_ahead = 5;
        if (isset($this->config))
        {
            $look_ahead = $this->config->look_ahead_range;
        }
        $in_X_days = ymd(mktime()+($look_ahead*87400)); // NOTE: using 87400 instead of 86400 to account for datetime math issues
        $my_signups = getSignupsBySignee($USER->id,$today,$in_X_days);
        if (! $my_signups)
        {
            $this->content->text .= "<div class=\"sus_very_small sus_indent\">You are not signed up for anything in the next $look_ahead days.</div>";
        } else 
        {
            $this->content->text .= "<h5>In the next $look_ahead days you've signed up for</h5>";
            $this->content->text .= generateMySignupsList($my_signups,false);
        }

        $signups_for_me = '';
        if (isset($USER->username))
        {
            $signups_for_me = getSignupsForSheetsOf($USER->id,$USER->username,$today,$in_X_days); // this is generating error log messages that $username is undefined - why isn't it set?
        }
        if (! $signups_for_me)
        {
            $this->content->text .= "<div class=\"sus_very_small sus_indent\">No sign-ups on your sheets in the next $look_ahead days.</div>";
        } else
        {
            $this->content->text .= "<h5>On your sheets in the next $look_ahead days</h5>";
            $this->content->text .=     generateSignupsForMeList($signups_for_me,false);
        }


        $this->content->text .= '</div>'; // end sus_block_body
/*
        $this->content->text .= '<h4><a href="'.$href.'&action=signup">'.get_string('nav_signup', 'block_signup_sheets')."</a></h4>\n";
        $this->content->text .= '<h5>'.get_string('coursesheets', 'block_signup_sheets').'</h5>';

        $this->content->text .= '<h4><a href="'.$href.'&action=managesheets">'.get_string('nav_mysheets', 'block_signup_sheets')."</a></h4>\n";

*/

        $this->content->text .= '
<script type="text/javascript">
$(document).ready(function()
{
  $(".sus_user_fullname").hover(
    function() {
      try
      {
        var d_id = ".signup_detail_info_"+$(this).attr("for_signup");
        //alert("d_id is " + d_id);
        //$(d_id).css("top",$(this).position().top);
        //$(d_id).css("left",$(this).position().left + $(this).width() + 4);
        $(d_id).css("top",$(this).position().top + $(this).height());
        $(d_id).css("left",$(this).position().left + 20);
        //$(this).css("color","#C0652C");
        //$(this).css("border-color","#C0652C");
      }
      catch(err)
      {
        alert("failure:"+err);
      }
    },
    function() {
      try
      {
        var d_id = ".signup_detail_info_"+$(this).attr("for_signup");
        //alert("oon_id is " + oon_id);
        $(d_id).css("left","-999px");
        //$(this).css("color","#000");
        //$(this).css("border-color","#000");
      }
      catch(err)
      {
        $(".sheet_details").css("left","-999px");
      }
    }
  );
});
</script>';

        // CSW TODO: change this to use new icon (or do away with stupid -hobbitses- icons.        
        //$icon_code = '<img src="image/pix/i/users.gif" class="icon" alt="" />';

        // example code from wmsroster:
//	if (is_null($this->config) || $this->config->flaglinknames) {
//         $this->content->items[] = $this->_roster_link('names',$currentcontext->id);
//          $this->content->icons[] = $icon_code;
//        }

        return $this->content;
    }

    function instance_allow_config() {
        return true;
    }

    // takes: none
    // does: performs the daily processes (sending reminder emails) for this block
    // returns: none
    // KEK: TEMPORARY DISABLE OF CRON 9/23/10 - this might be the culprit of the server horkage
    // CSW 2010/10/12: re-enabled cron, hopfully works OK now... (using simple emailer)
    // CSW 2011/06/20: re-enabling moodler emailer - looks like the problems we were seeing are unrelated to the email process
    //function _cron_disabled()
    function cron()
    {
        //global $USER_FIELDS,$SIGNUP_FIELDS,$OPENING_FIELDS,$SHEET_FIELDS,$SHEET_GROUP_FIELDS;;
//        $from = "glow_sus_reminders@williams.edu";
//        $subject_my = "Glow SUS - Your upcoming signups";
//        $subject_for_me = "Glow SUS - Upcoming sign-ups on your sheets";
        $from = sus_reminders_email_address();
        $subject_my = sus_block_name()." - Your upcoming signups";
        $subject_for_me = sus_block_name()." - Upcoming sign-ups on your sheets";

        $messagetext = "this is a test";

	if (!empty($CFG->noemailever)) {
	  // hidden setting for development sites, set in config.php if needed
	  return true;
	}


        // 1. get all signup data, grouped by user
        //   NOTE: this may require adding reminder_sent fields to the sign-ups table to track signee and admin reminders
        // 2. cycle through data. for each new user:
        //   2.1. email_to_user($user, $from, $subject, $messagetext);
        //   2.2. accumulate list of signee and admin reminders send
        // 3. post loop, send and accumulate as above for the last accumulated user
        // 4. update all sign-ups in accumulated list to reflect that reminders have been sent

	// 5. analagous to 1-4 above, but for sheet owners/admins rather than signees as message recipient

        $user_complex = getStructuredUserSignups(mktime(),mktime()+(86400 * 3)+1000);

        // remind users about openings for which they've signed up
        foreach ($user_complex as $uc)
        {
            $msg = "This is a reminder of the upcoming openings for which you've signed up:\n";
            //$last_date = $uc->signups[0]->opening->o_date_y_m_d;
            $last_date = '';
            foreach ($uc->signups as $su)
            {
                if ($last_date != $su->opening->o_date_y_m_d)
                {
                    if ($last_date != '') {$msg .= "\n";}
                    $last_date = $su->opening->o_date_y_m_d;
                    $msg .= "\n";
                    if ($su->opening->o_date_y_m_d == ymd('','-'))
                    {
                        $msg .= "TODAY! ";
                    }
                    $msg .= "{$su->opening->o_date_y_m_d}";
                }
                $msg .= "\n  {$su->opening->o_begin_time_h_m_p} to {$su->opening->o_end_time_h_m_p}";
                $msg .= ($su->opening->o_location?" at {$su->opening->o_location}\n   ":'');
                $msg .= " for {$su->opening->sheet->s_name}\n";
                if ($su->opening->sheet->s_description)
                {
                    $msg .= "    {$su->opening->sheet->s_description}\n";
                }
                if ($su->opening->o_name)
                {
                    $msg .= "    {$su->opening->o_name}\n";
                }
                if ($su->opening->o_description)
                {
                    $msg .= "    {$su->opening->o_description}\n";
                }
            }
	    // now mail out the message
	    // 1. check prefs
	    // 2. send message - NOTE: the moodle function email_to_user seems to have issues, so use a different mailer?
	    if (!empty($uc->emailstop)) {
	      $mailresult = email_to_user($uc, $from, $subject_my, $msg);
	      
	      // CSW 2010/10/13 - experimenting with disabling mail entirely
	      // CSW 2011/06/22 - going back to the moodler emailer above
	      //$mailresult = simpleEmail($uc->email,$subject_my, $msg);
	      //$mailresult = simpleEmail('cwarren@williams.edu',$subject_my, $msg);
	      flush();
	      sleep(2);
	    }

        }

        // remind users about sign-ups on sheets they own or admin
        $user_complex = getStructuredSignupsForUsers(mktime(),mktime()+(86400 * 3)+1000);

        foreach ($user_complex as $uc)
        {
            $msg = "This is a reminder of upcoming sign-ups on sheets you own or manage:\n\n";
            //$last_date = $uc->openings[0]->o_date_y_m_d;
            $last_date = '';
            foreach ($uc->openings as $o)
            {
                if ($last_date != $o->o_date_y_m_d)
                {
                    if ($last_date != '') { $msg .= "\n"; }
                    $last_date = $o->o_date_y_m_d;
                    if ($o->o_date_y_m_d == ymd('','-'))
                    {
                        $msg .= "TODAY! ";
                    }
                    $msg .= "{$o->o_date_y_m_d}\n";
                }
                $msg .= "  {$o->o_begin_time_h_m_p} to {$o->o_end_time_h_m_p} for {$o->sheet->s_name}";
                if ($o->sheet->s_for_me_type == 'adminned')
                {
                    $msg .= " (a sheet you manage)";
                }
                $msg .= "\n";
                if ($o->sheet->s_description)
                {
                    $msg .= "    {$o->sheet->s_description}\n";
                }
                if ($o->o_name)
                {
                    $msg .= "    {$o->o_name}\n";
                }
                if ($o->o_description)
                {
                    $msg .= "    {$o->o_description}\n";
                }
                foreach ($o->signups as $su)
                {
                    //print_r($su);
                    //echo "\n";
                    $msg .= "        {$su->susr->susr_firstname} {$su->susr->susr_lastname} ({$su->susr->susr_username}, {$su->susr->susr_email})\n";
                }
                $msg .= "\n";
            }
	    // now mail out the message
	    // 1. check prefs
	    // 2. send message - NOTE: the moodle function email_to_user seems to have issues, so use a different mailer?
	    if (!empty($uc->emailstop)) {
              $mailresult = email_to_user($uc, $from, $subject_for_me, $msg);


	      // CSW 2010/10/13 - experimenting with disabling mail entirely
	      // CSW 2011/06/22 - going back to the moodler emailer above
	      //$mailresult = simpleEmail($uc->email,$subject_for_me, $msg);
	      //$mailresult = simpleEmail('cwarren@williams.edu',$subject_for_me, $msg);
	      flush();
	      sleep(2);
	    }

        }

        //doesn't seem to update last cron automatically - what a SAD, BAD, LAME system!
        $susblock=get_record('block','name','signup_sheets');
        $susblock->lastcron=mktime();
        update_record('block',$susblock);
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////
    // private functions (NOTE: start names with _ (e.g. _my_func)

}

?>