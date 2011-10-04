<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

// This library holds all the routines necessary for display of a signup sheet calendar

/*
////////////////////////////////////////////////////////////////////////////////////////

# takes:
# does
# returns:
function f($p)
{
    return $value;
}
*/

////////////////////////////////////////////////////////////////////////////////////////

# returns: a string that is the headers for days of the week (sun-sat)
function dowHeaders()
{
    return '<div class="week_row">'.
           '<div class="day_cell day_header">Sun</div>'.
           '<div class="day_cell day_header">Mon</div>'.
           '<div class="day_cell day_header">Tue</div>'.
           '<div class="day_cell day_header">Wed</div>'.
           '<div class="day_cell day_header">Thu</div>'.
           '<div class="day_cell day_header">Fri</div>'.
           '<div class="day_cell day_header">Sat</div>'.
           "</div>\n";
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: first real day of week (1==mon through 7==sun)
# returns: html for starting week and blank cells to first day
function startWeekAt($first_real_day)
{
    $text = '<div class="week_row">';
    if ($first_real_day == 1)
    {
      return $text;
    }
    $dcount = 1;
    while ($dcount < $first_real_day)
    {
      $text .= '<div class="day_cell cal_gone"></div>';
      $dcount++;
    }
    return $text;
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: first real day of week (1==mon through 7==sun)
# returns: html for blank cells to the end of the week, then closing the week
function finishWeekFrom($last_real_day)
{
    if ($last_real_day == 0)
    {
      return "</div>\n";
    }
    $text = '';
    $dcount = 7;
    while ($last_real_day < $dcount)
    {
      $text .= '<div class="day_cell cal_gone"></div>';
      $dcount--;
    }
    $text .=  "</div>\n";
    return $text;
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: an opening object (complex - as from getStructuredSheetData in sus_lib)
# returns: HTML for display of that object
function openingDisplay($opening,$for_admin_flag=false,$private_signups_flag=true)
{
    debug_r(5,$opening);
    debug_r(5,"for_admin_flag is $for_admin_flag");
    debug_r(5,"private_signups_flag is $private_signups_flag");
    //debug_r(3," is $");
    global $CFG,$USER;

    $is_future = (($opening->o_begin_datetime > mktime())?'yes':'no');
    $ret = '<div class="opening opening_'.$opening->o_id.'" is_future="'.$is_future.'">';
    $ret .= '<div class="opening_header_info'.(isset($opening->signups_by_user[$USER->id])?' user_is_signee':'').'">';
    // remove opening link, but only on sheet admin page
    if ($_REQUEST['action'] == 'editsheet'){

       $ret .= '<img class="edit_opening_link" for_day ="' . ymd($opening->o_begin_datetime) . '" for_opening="' . $opening->o_id;
       $ret .= '" alt="edit opening" title="edit opening" src="image/pix/t/edit.gif"> ';

       $ret .= '<img class="remove_opening_link" for_day ="' . ymd($opening->o_begin_datetime) . '" for_opening="' . $opening->o_id;
       $ret .= '" for_sheet="'.$opening->o_sus_sheet_id.'" alt="delete opening" title="delete opening" src="image/pix/t/delete.png">';
    }
    $ret .= '<span class="opening_day">'.$opening->o_date_y_m_d
            .'</span> <span class="opening_timerange">'.$opening->o_begin_time_h_m_p
            .' - '.$opening->o_end_time_h_m_p."</span>\n";
    $capacity_class = 'opening_has_space';
    $num_signups = count($opening->signups);
    if (($num_signups >= $opening->o_max_signups) && ($opening->o_max_signups >= 1))
    {
        $capacity_class = 'opening_is_full';
    }
    $capacity_text = $opening->o_max_signups;
    if ($capacity_text < 1)
    {
        $capacity_text = '*';
    }
    $ret .= '<span class="opening_capacity '.$capacity_class.'">'."({$num_signups}/$capacity_text)</span>\n";
    if ($for_admin_flag)
    {
       //$ret .= '<div class="opening_signup_link_box edit_opening_link">add someone</div>';
       $ret .= '<div class="opening_signup_link_box edit_opening_link" for_day ="' . ymd($opening->o_begin_datetime) . '" for_opening="' . $opening->o_id;
       $ret .= '" title="add someone" action="addsomeone">add someone</div>';

       // TODO: implement functionality to allow admin to sign up somone else
       // CSW 2010/01/12 - decided to move this functionality to the opening edit page
    } else
    {
        if (isset($opening->signups_by_user[$USER->id]))
        {
            $ret .= "<span class=\"msg_signed_up\">you're signed up</span>";
            if ($is_future == 'yes')
            {
                $ret .= "<img class=\"remove_signup_link nukeit\" src=\"image/pix/t/delete.png\" alt=\"remove signup\" title=\"remove signup\" for_signup=\"{$opening->signups_by_user[$USER->id]->su_id}\" for_opening=\"{$opening->o_id}\" for_sheet=\"{$opening->o_sus_sheet_id}\" />";
            }
        } else
        {
            if ((($num_signups < $opening->o_max_signups) || ($capacity_text == '*')) && ($is_future == 'yes'))
            {
                $ret .= "<div class=\"opening_signup_link_box\"><div class=\"opening_signup_link\" for_opening=\"{$opening->o_id}\"><span class=\"long_sign_up\">sign up for this opening</span><span class=\"short_sign_up\">sign up</span></div></div>\n";
            }
        }
    }
    $ret .= "</div>"; // close opening_header_info
    $ret .= ($opening->o_name?"<div class=\"opening_name\">$opening->o_name</div>":'');
    if (isset($opening->o_description)){
       $ret .= ($opening->o_description?"<div class=\"opening_description\">{$opening->o_description}</div>\n":'');
    }
    if (($for_admin_flag || (! $private_signups_flag)) && ($num_signups > 0))
    //if ($for_admin_flag  || (! $private_signups_flag))
    {
      $ret .= '<ul class="opening_signees_list" for_opening="'.$opening->o_id.'">'."\n";
      foreach ($opening->signups as $su)
      {
          $ret .= "  <li class=\"signee_list_item\" signup_id=\"{$su->su_id}\"\>{$su->user->usr_firstname} {$su->user->usr_lastname}";
          $ret .= "<div class=\"list_su_signup_details\"><i>".ymd_hm_a($su->su_created_at,'-')."</i><br/>";
          if (($for_admin_flag) && ($su->su_admin_comment))
          {
              $ret .= $su->su_admin_comment;
          }
          $ret .= "</div>";
          $ret .= "</li>\n";
      }
      $ret .= '</ul>';
    }
    $ret .= "</div>\n";

    return $ret;
}


////////////////////////////////////////////////////////////////////////////////////////

# takes: a date as a timestamp, a ymd for the earliest active date, a ymd for the latest active date
# returns: a string which is the list of classes to apply to the cell for that day
function getDayCellClass($t,$start_active_ymd,$stop_active_ymd)
{

    $t_ymd = ymd($t);
    $today_ymd = ymd();

    debug(6,"t is $t");
    debug(6,"t_ymd is $t_ymd");
    debug(6,"start_active_ymd is $start_active_ymd");
    debug(6,"stop_active_ymd is $stop_active_ymd");

    $day_class='day_cell';
    if (($t_ymd < $start_active_ymd) || ($t_ymd > $stop_active_ymd))
    {
        if (isWeekend($t))
        {
            $day_class .= ' cal_inactive_we';
        } else
        {
            $day_class .= ' cal_inactive_wd';
        }
    } else
    {
        if (isWeekend($t))
        {
            if ($t_ymd < $today_ymd)
            {
                $day_class .= ' cal_past_we';
            } else
            {
                $day_class .= ' cal_we';
            }
        } else
        {
            if ($t_ymd < $today_ymd)
            {
                $day_class .= ' cal_past_wd';
            }
        }
    }

    if ($t_ymd == $today_ymd)
    {
        $day_class .= ' cal_today';
    }

    return $day_class;
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: none
# returns: a 24 item array, prepolulated with 0's
function getNewOpeningMinitimes()
{
    $minitimes = array();
    for ($i = 0; $i < 24; $i++)
    {
        $minitimes[] = 0;
    }

    return $minitimes;
}

# takes: a string to use as an HTML id, a 24 elt array of 0's and 1's
# returns: an HTML structure of divs reflecting that array
function getOpeningMinitimesDisplay($for_date,$minitimes,$details)
{
    debug_r(6,$minitimes);
    $html = "<div id=\"opening_minitimes_for_$for_date\" class=\"day_openings_minitimes\" for_date=\"$for_date\">\n";
    $counter = 0;
    foreach ($minitimes as $mt)
    {
        if ($counter == 12)
        {
          $html .= "  <div class=\"noon_split\"></div>\n";
        }
        if (($counter == 7) || ($counter == 18))
        {
          // removed for now - looks a little cluttered. Consider coloring the full range instead
          // $html .= "  <div class=\"workday_split\"></div>\n";
        }
        $html .= "  <div class=\"".($mt==1?'is_full':'is_free')."\"></div>\n";
        $counter++;
    }
    $html .= $details."\n";
    $html .= "</div>\n";

    return $html;
}


////////////////////////////////////////////////////////////////////////////////////////

# takes:
#   a structured sheet data object
#   an optional url for creating opening links
#   optional text for create/add opening links
#   a flag for direct printing/display (defaults to false)
# does: displays the calendar if the $direct_display flag is true
# returns: the calendar code if $direct_display is false, and ture (if anything was displayed) or false (nothing displayed) if it is $direct_display is true
function getCalendar($sheet_complex,$create_opening_url='',$add_opening_text='',$direct_display=false)
{

    $cal_display_html = '<div class="full_calendar">';
    $day_counter = 0;

    $opening_index = 0;
    $openings_list_display = '';

    // these need to reflect the CSS values - ugly hack, I know...
    $day_height = 36;
    $dow_heading_height = 18;
    $cell_border = 1;
    $cell_padding = 4;

    $height_of_day_cells = $day_height + 2*$cell_border + 2*$cell_padding;
    $height_of_dow_header = $dow_heading_height + 2*$cell_border + 2*$cell_padding;

    $cal_start = firstOfMonth_timeAsDate($sheet_complex->s_date_opens);
    $sus_start_ymd = ymd($sheet_complex->s_date_opens);
    $cal_cur = $cal_start;
    $cal_end = lastOfMonth_timeAsDate($sheet_complex->s_date_closes);
    $sus_end_ymd = ymd($sheet_complex->s_date_closes);
    $today_ymd = ymd();

    //log_debug(0,$cal_start);
    //log_debug(0,$cal_start_ymd);
    //log_debug(0,$cal_end);
    //log_debug(0,$cal_end_ymd);

    // Handle case where there are openings prior to the cal start by
    // skipping earlier openings - this happend, e.g., when a user updates
    // a sheet from a prior semester to start at the current semester but
    // they still have many openings from the old semester.
    $loop_limit = 10000; // just in case something unforeseen causes an otherwise infinite loop
    if (property_exists($sheet_complex,'openings') && (count($sheet_complex->openings) >= 0)) {
        while (($opening_index < $loop_limit) && ($sheet_complex->openings[$opening_index]->o_dateymd < $sus_start_ymd)) {
            $opening_index++;
        }
    }
    if ($opening_index >= $loop_limit) {
      log_debug(0,"Sign-up Sheet WARNING: possible infinite loop scenario aborted; opening_index > loop_limit in cal_lib");
    }

    // now do that actual calendar
    while ($cal_cur <= $cal_end)
    {
        $cal_cur_dow = (date('N',$cal_cur) % 7) + 1; // shift sunday to first day of week
        $cal_cur_ymd = ymd($cal_cur);
        $cal_cur_y_m_d = ymd($cal_cur,'-');

        if (isFirstOfMonth($cal_cur)) // close out the previous month and set up a new one
        {
            if ($day_counter > 0)
            {
                $cal_display_html .=  finishWeekFrom(date('N',$cal_cur)%7);
                $cal_display_html .=  '</div>'; // close month grid
            }

            // num rows in a month = (first dow (0 based) + last day in month + 7) div 7
            $num_week_rows = getNumWeekRowsInMonth($cal_cur);
            $mon_vert_size = ($num_week_rows * $height_of_day_cells) + $height_of_dow_header - (2*$cell_border);
            $month_title = implode('<br/>',str_split(strtoupper(date('M Y',$cal_cur))));

            $cal_display_html .=  '<div class="month_title"  style="height: '.$mon_vert_size.'px;"><p>'.$month_title.'</p></div>';
            $cal_display_html .=  '<div class="month_grid" style="height: '.$mon_vert_size.'px;">';
            $cal_display_html .=  dowHeaders();
            $cal_display_html .=  startWeekAt($cal_cur_dow);
            $day_counter = $cal_cur_dow;
        }

        // get info for the day
        $day_text = date('j',$cal_cur);
        $day_class = getDayCellClass($cal_cur,$sus_start_ymd,$sus_end_ymd);
        $openings_info_list = '';
        $openings_mini_times = getNewOpeningMinitimes();
        //print_r($sheet_complex); // DEBUGGING

        if (isset($sheet_complex->openings) && $sheet_complex->openings)
        {
	  //log_debug(-1,"opening_index = ".$opening_index);
	  // log_debug(-1,"count(sheet_complex->openings) = ". count($sheet_complex->openings));
	  //log_debug(-1,"sheet_complex->openings[opening_index]->o_dateymd = ".$sheet_complex->openings[$opening_index]->o_dateymd);
	  //log_debug(-1,"cal_cur_ymd = ".$cal_cur_ymd);
            while (($opening_index < count($sheet_complex->openings)) && ($sheet_complex->openings[$opening_index]->o_dateymd == $cal_cur_ymd))
            {
              $openings_info_list .= '<li>'.openingDisplay($sheet_complex->openings[$opening_index],($create_opening_url && $add_opening_text),$sheet_complex->s_flag_private_signups).'</li>';
              if (preg_match('/(\d+):(\d+)/',$sheet_complex->openings[$opening_index]->o_begin_time_h24_m,$matches))
              {
                  debug(5,"matches[0] is ".$matches[0]);
                  debug(5,"matches[1] is ".$matches[1]);
                  debug(5,"matches[1] + matches[2]/60 is ".($matches[1] + $matches[2]/60));
                  $eft_hour = round($matches[1] + $matches[2]/60);
                  debug(5,"eft_hour is $eft_hour");
                  if ($eft_hour == 24) { $eft_hour = 23; }
                  $openings_mini_times[$eft_hour] = 1;
                  $dur = $sheet_complex->openings[$opening_index]->o_dur_seconds / 3600; // duration in hours
                  debug(5,"dur is $dur");
                  while (($eft_hour < 23) && ($dur > 1))
                  {
                      $eft_hour++;
                      $dur--;
                      $openings_mini_times[$eft_hour] = 1;
                  }
              } else
              {
                  error("failed to match time in {$sheet_complex->openings[$opening_index]->o_begin_time_h24_m}");
              }
              $opening_index++;
            }
            if ($openings_info_list)
            {
                $day_class .= ' day_has_openings';
            }
        }
        $add_opening = '';
        if ($create_opening_url && $add_opening_text && (! preg_match('/inactive/',$day_class)) )
        {
            $add_opening = link_to_popup_window ($create_opening_url.'&day='.$cal_cur_ymd.'&action=newopening', 'createopening', $add_opening_text,
                                        480, 640, 'Create Sign-up Openings',
                                        'location=0,directories=0,menubar=0,scrollbars=1',true); // dev code - shows scroll bars, for live set to 0
        }

        // actually output the day cell
        //$cal_display_html .=  '<div id="day_'.ymd($cal_cur).'" class="'.$day_class.'">'. $day_text . $add_opening;
        $cal_display_html .=  '<div id="day_'.ymd($cal_cur).'" class="'.$day_class.'">';
        if ($openings_info_list)
        {
            //$cal_display_html .=  "( )";
            $openings_info = '<div id="openings_on_'.$cal_cur_ymd.'" class="openings_summary_box">'
                           .'<div class="opening_day_heading">'.$cal_cur_y_m_d.'</div>'
                           .'<ul class="openings_list openings_list_'.$cal_cur_ymd.'">'.$openings_info_list.'</ul>'
                        ."</div>\n";
            //$cal_display_html .=  getOpeningMinitimesDisplay($cal_cur_ymd,$openings_mini_times) . $openings_info;
            $cal_display_html .=  getOpeningMinitimesDisplay($cal_cur_ymd,$openings_mini_times,$openings_info);
        }
        $cal_display_html .=  $day_text . '<br/>'. $add_opening;
        $cal_display_html .=  '</div>'; // <!-- end day cell -->

        // end week if necessary
        if (($day_counter > 0) && ($day_counter % 7 == 0))
        {
           $cal_display_html .=  "</div>\n".'<div class="week_row">';
        }

        // finished with this day, on to the next
        $day_counter++;
        $cal_cur = dayAfter_timeAsDate($cal_cur);
    } // end while ($cal_cur <= $cal_end)

    $cal_display_html .=  finishWeekFrom(date('N',$cal_cur)%7);
    //$cal_display_html .=  dowHeaders();
    $cal_display_html .=  "</div>\n"; // end month grid
    $cal_display_html .=  "</div>\n"; // end full calendar

    if ($cal_display_html && $direct_display)
    {
        echo $cal_display_html;
        return ($cal_display_html != '');
    }
    return $cal_display_html;
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: an array of opening objects, an optional flag to indicate the list should be display instead of returned
# returns: the HTML for the openings in list format if
#    $direct_display=false, else if $direct_display=true then treu if
#    anything was displayed and false otherwise
function getOpeningsList($openings,$sheet_start_ymd,$create_opening_url='',$add_opening_text='',$direct_display=false,$private_signups_flag=true)
{
    $openings_html = "<ul>\n";
    $opening_index = 0;
    $openings_on_a_day = '';
    $prior_ymd = '';
    $num_openings = count($openings);

    if ($num_openings > 0) {
      while ($openings[$opening_index]->o_dateymd < $sheet_start_ymd) {
	$opening_index++;
      }
    }

    while ($opening_index < $num_openings)
    {
        $cur = $openings[$opening_index];

        if ($prior_ymd == '') {$prior_ymd = $cur->o_dateymd;}

        if ($prior_ymd != $cur->o_dateymd)
        {
            $heading_date = substr($prior_ymd,0,4).'-'.substr($prior_ymd,4,2).'-'.substr($prior_ymd,6,2);
            $openings_html .=  "  <li>
    <div id=\"openings_on_$prior_ymd\">
      <div class=\"opening_day_heading\">$heading_date</div>
      <ul class=\"openings_list openings_list_$prior_ymd\">
$openings_on_a_day
      </ul>
    </div>
  </li>
";
            $prior_ymd = $cur->o_dateymd;
            $openings_on_a_day = '';
        }

        $openings_on_a_day .= '        <li>'.openingDisplay($openings[$opening_index],($create_opening_url && $add_opening_text),$private_signups_flag)."</li>\n";

        $opening_index++;
    }
    // don't forget to tack on the final accumulated info....
    $heading_date = substr($prior_ymd,0,4).'-'.substr($prior_ymd,4,2).'-'.substr($prior_ymd,6,2);
    $openings_html .=  "  <li>
    <div id=\"openings_on_$prior_ymd\">
      <div class=\"opening_day_heading\">$heading_date</div>
      <ul class=\"openings_list openings_list_$prior_ymd\">
$openings_on_a_day
      </ul>
    </div>
  </li>
";
    $openings_html .= "</ul>\n";

    if ($direct_display && $openings_html)
    {
        echo $openings_html;
        return ($openings_html != '');
    }
    return $openings_html;
}

////////////////////////////////////////////////////////////////////////////////////////

?>