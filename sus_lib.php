<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

////////////////////////////////////////////////////////////////////////////////////////
// constants

$SHEET_GROUP_FIELDS = 'sg.id  AS sg_id
,sg.created_at  AS sg_created_at
,sg.updated_at  AS sg_updated_at
,sg.flag_deleted  AS sg_flag_deleted
,sg.owner_user_id  AS sg_owner_user_id
,sg.flag_is_default AS sg_flag_is_default
,sg.name  AS sg_name
,sg.description  AS sg_description
,sg.max_g_total_user_signups  AS sg_max_g_total_user_signups
,sg.max_g_pending_user_signups  AS sg_max_g_pending_user_signups';

$SHEET_FIELDS = 's.id  AS s_id
,s.created_at  AS s_created_at
,s.updated_at  AS s_updated_at
,s.flag_deleted  AS s_flag_deleted
,s.owner_user_id  AS s_owner_user_id
,s.last_user_id  AS s_last_user_id
,s.sus_sheetgroup_id  AS s_sus_sheetgroup_id
,s.name  AS s_name
,s.description  AS s_description
,s.type  AS s_type
,s.date_opens  AS s_date_opens
,s.date_closes  AS s_date_closes
,s.max_total_user_signups  AS s_max_total_user_signups
,s.max_pending_user_signups  AS s_max_pending_user_signups
,s.flag_alert_owner_change  AS s_flag_alert_owner_change
,s.flag_alert_owner_signup  AS s_flag_alert_owner_signup
,s.flag_alert_owner_imminent  AS s_flag_alert_owner_imminent
,s.flag_alert_admin_change  AS s_flag_alert_admin_change
,s.flag_alert_admin_signup  AS s_flag_alert_admin_signup
,s.flag_alert_admin_imminent  AS s_flag_alert_admin_imminent
,s.flag_private_signups AS s_flag_private_signups';

$OPENING_FIELDS = "o.id  AS o_id
,o.created_at  AS o_created_at
,o.updated_at  AS o_updated_at
,o.flag_deleted  AS o_flag_deleted
,o.last_user_id  AS o_last_user_id
,o.sus_sheet_id  AS o_sus_sheet_id
,o.opening_set_id  AS o_opening_set_id
,o.name  AS o_name
,o.description  AS o_description
,o.max_signups  AS o_max_signups
,o.admin_comment  AS o_admin_comment
,o.begin_datetime  AS o_begin_datetime
,o.end_datetime  AS o_end_datetime
,o.end_datetime - o.begin_datetime AS o_dur_seconds
,FROM_UNIXTIME(o.begin_datetime,'%Y%m%d') AS o_dateymd
,FROM_UNIXTIME(o.begin_datetime,'%Y-%m-%d') AS o_date_y_m_d
,FROM_UNIXTIME(o.begin_datetime,'%l:%i %p') AS o_begin_time_h_m_p
,FROM_UNIXTIME(o.end_datetime,'%l:%i %p') AS o_end_time_h_m_p
,FROM_UNIXTIME(o.begin_datetime,'%k:%i') AS o_begin_time_h24_m
,FROM_UNIXTIME(o.end_datetime,'%k:%i') AS o_end_time_h24_m
,o.location  AS o_location";

$SIGNUP_FIELDS = 'su.id AS su_id
,su.created_at AS su_created_at
,su.updated_at AS su_updated_at
,su.flag_deleted AS su_flag_deleted
,su.last_user_id AS su_last_user_id
,su.sus_opening_id AS su_sus_opening_id
,su.signup_user_id AS su_signup_user_id
,su.admin_comment AS su_admin_comment';

$ACCESS_FIELDS = 'ac.id AS a_id
,ac.created_at AS a_created_at
,ac.updated_at AS a_updated_at
,ac.last_user_id AS a_last_user_id
,ac.sheet_id AS a_sheet_id
,ac.type AS a_type
,ac.constraint_id AS a_constraint_id
,ac.constraint_data AS a_constraint_data
,ac.broadness AS a_broadness';

$USER_FIELDS = 'usr.id
 ,usr.auth
 ,usr.confirmed
 ,usr.policyagreed
 ,usr.deleted
 ,usr.mnethostid
 ,usr.username
 ,usr.password
 ,usr.idnumber
 ,usr.firstname
 ,usr.lastname
 ,usr.email
 ,usr.emailstop
 ,usr.icq
 ,usr.skype
 ,usr.yahoo
 ,usr.aim
 ,usr.msn
 ,usr.phone1
 ,usr.phone2
 ,usr.institution
 ,usr.department
 ,usr.address
 ,usr.city
 ,usr.country
 ,usr.lang
 ,usr.theme
 ,usr.timezone
 ,usr.firstaccess
 ,usr.lastaccess
 ,usr.lastlogin
 ,usr.currentlogin
 ,usr.lastip
 ,usr.secret
 ,usr.picture
 ,usr.url
 ,usr.description
 ,usr.mailformat
 ,usr.maildigest
 ,usr.maildisplay
 ,usr.htmleditor
 ,usr.ajax
 ,usr.autosubscribe
 ,usr.trackforums
 ,usr.timemodified
 ,usr.trustbitmask
 ,usr.imagealt
 ,usr.screenreader';

////////////////////////////////////////////////////////////////////////////////////////

# takes: a value
# returns: a version of that value ready to be used in a SQL statement (quoted as approp)
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc())
   {
       $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value) || $value[0] == '0')
   {       
       $value = preg_replace('/\\\\\'/',"''",mysql_real_escape_string($value));
       $value = "'" . $value . "'";
   }
   return $value;
}


////////////////////////////////////////////////////////////////////////////////////////

# takes: none
# modifies the $USER object to include info about the user's sign-ups - the number of sign-ups total and future by group and sheet
# returns: none
function setUserSignupInfo()
{
    global $USER,$CFG;

   $sql = "
SELECT
   su.id AS su_id
  ,o.id AS o_id
  ,o.begin_datetime AS o_begin_datetime
  ,s.id AS s_id
  ,sg.id AS sg_id
FROM
  {$CFG->prefix}sus_signups AS su
  JOIN {$CFG->prefix}sus_openings AS o ON o.id = su.sus_opening_id AND o.flag_deleted != 1
  JOIN {$CFG->prefix}sus_sheets AS s ON s.id = o.sus_sheet_id AND s.flag_deleted != 1
  JOIN {$CFG->prefix}sus_sheetgroups AS sg ON sg.id = s.sus_sheetgroup_id AND sg.flag_deleted != 1
WHERE
  su.signup_user_id = {$USER->id}
  AND su.flag_deleted != 1";

    $sinfo = sus_get_records_sql($sql);

    if (! $sinfo)
    {
        return;
    }

    $USER->signups = array('total'  => array('groups'=>array(),'sheets'=>array()),
                           'future' => array('groups'=>array(),'sheets'=>array()));

    $now = mktime();
    foreach ($sinfo as $si)
    {
        if (isset($USER->signups['total']['groups'][$si->sg_id]))
        {
            $USER->signups['total']['groups'][$si->sg_id]++;
        } else
        {
            $USER->signups['total']['groups'][$si->sg_id] = 1;
        }
        if (isset($USER->signups['total']['sheets'][$si->s_id]))
        {
            $USER->signups['total']['sheets'][$si->s_id]++;
        } else
        {
            $USER->signups['total']['sheets'][$si->s_id] = 1;
        }
        if ($si->o_begin_datetime > $now)
        {
            if (isset($USER->signups['future']['groups'][$si->sg_id]))
            {
                $USER->signups['future']['groups'][$si->sg_id]++;
            } else
            {
                $USER->signups['future']['groups'][$si->sg_id] = 1;
            }
            if (isset($USER->signups['future']['sheets'][$si->s_id]))
            {
                $USER->signups['future']['sheets'][$si->s_id]++;
            } else
            {
                $USER->signups['future']['sheets'][$si->s_id] = 1;
            }
        }
    }

}

////////////////////////////////////////////////////////////////////////////////////////

# takes: an optional group id (pass in 0 to skip), and an optional sheet id
# returns: an array of sheet info objects (sheet group (de-nomralized) and sheet (outer joined)) for the current user
function getOwnedSheetsAndAssociatedInfo($group_id=0,$sheet_id=0)
{
    # get current user id
    global $USER, $CFG, $SHEET_GROUP_FIELDS,$SHEET_FIELDS;

    # get a list of all the user's sheets, and the group each one is in

    $sql = "
SELECT 
$SHEET_GROUP_FIELDS
,$SHEET_FIELDS
FROM
  {$CFG->prefix}sus_sheetgroups AS sg 
  LEFT OUTER JOIN {$CFG->prefix}sus_sheets AS s 
      ON s.sus_sheetgroup_id = sg.id 
     AND s.owner_user_id = sg.owner_user_id
     AND s.flag_deleted != 1"
.($sheet_id?"\n     AND s.id = $sheet_id":'')."
WHERE sg.flag_deleted != 1
  AND sg.owner_user_id = $USER->id"
.($group_id?"  AND sg.id=$group_id":'')
."
ORDER BY
  sg_name
 ,sg_id
 ,s_name
 ,s_created_at
";

    debug(4,"\n\nsql is $sql\n\n");

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: an optional group id (pass in 0 to skip), and an optional sheet id
# returns: an array of sheet info objects (sheet group (de-nomralized) and sheet (outer joined)) for the current user has admin access and does NOT own
function getAdminSheetsAndAssociatedInfo($group_id=0,$sheet_id=0)
{
    # get current user id
    global $USER, $CFG, $SHEET_GROUP_FIELDS,$SHEET_FIELDS;

    # get a list of all the user's sheets, and the group each one is in

    $sql = "
SELECT 
$SHEET_GROUP_FIELDS
,$SHEET_FIELDS
,usr.firstname AS usr_firstname
,usr.lastname AS usr_lastname
FROM
  {$CFG->prefix}sus_sheetgroups AS sg 
  LEFT OUTER JOIN {$CFG->prefix}sus_sheets AS s 
      ON s.sus_sheetgroup_id = sg.id 
     AND s.owner_user_id = sg.owner_user_id
     AND s.flag_deleted != 1
".($sheet_id?"     AND s.id = $sheet_id":'')."
  JOIN {$CFG->prefix}user AS usr ON usr.id = s.owner_user_id
  JOIN {$CFG->prefix}sus_access AS a ON a.sheet_id = s.id
     AND a.type = 'adminbyuser'
     AND a.constraint_data = '{$USER->username}'
WHERE sg.flag_deleted != 1
  AND s.owner_user_id != $USER->id
".($group_id?"  AND sg.id=$group_id":'');

    debug(4,"\n\nsql is $sql\n\n");

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an optional flag for whether access data should be included in the results
// returns: an array of sheet objects on which the current user can sign up
function getSignupAccessibleSheets($includeAccessRecords=true,$for_user_id=0,$for_sheet_id=0,$for_access_id=0)
{
    global $USER, $CFG, $SHEET_FIELDS, $ACCESS_FIELDS;

    if ($for_user_id<1)
    {
        $for_user_id = $USER->id;
    }

    // if filtering on the access id, need to get it in the query
    if ($for_access_id > 0)
    {
        $includeAccessRecords=true;
    }

    $sql ="
SELECT
$SHEET_FIELDS".
($includeAccessRecords?"
,$ACCESS_FIELDS":'')."
FROM
{$CFG->prefix}sus_sheets AS s
JOIN (
    SELECT DISTINCT
      a.sheet_id".
($includeAccessRecords?'
      ,a.id AS id
      ,a.created_at AS created_at
      ,a.updated_at AS updated_at
      ,a.last_user_id AS last_user_id
      ,a.type AS type
      ,a.constraint_id AS constraint_id
      ,a.constraint_data AS constraint_data
      ,a.broadness AS broadness':'')."
    FROM
      {$CFG->prefix}sus_access AS a
    WHERE
      (a.type='byhasaccount')
      OR
      (a.type='byuser'
       AND (a.constraint_data = '{$USER->username}' OR a.constraint_id=$for_user_id))
      OR
      (a.type='byrole'
       AND a.constraint_data = 'teacher'
       AND $for_user_id IN (
          SELECT DISTINCT
            usr.id
          FROM
            {$CFG->prefix}role_assignments AS r_asg
            JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
            JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
            JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
          WHERE
            role.name in ('editingteacher','teacher')
            AND ctx.contextlevel =  ". CONTEXT_COURSE."
            AND usr.id = $for_user_id))
      OR
      (a.type='byrole'
       AND a.constraint_data = 'student'
       AND $for_user_id IN (
          SELECT DISTINCT
            usr.id
          FROM
            {$CFG->prefix}role_assignments AS r_asg
            JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
            JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
            JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
          WHERE
            role.name in ('student','auditor_student')
            AND ctx.contextlevel =  ". CONTEXT_COURSE."
            AND usr.id = $for_user_id))
      OR
      (a.type='bycourse'
       AND a.constraint_id IN (
          SELECT DISTINCT
            ctx.instanceid
          FROM
            {$CFG->prefix}role_assignments AS r_asg
            JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
            JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
            JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
          WHERE
            role.shortname in ('student','auditor','editingta','noneditingta','teacher','editingteacher')
            AND ctx.contextlevel =  ". CONTEXT_COURSE."
            AND usr.id = $for_user_id))
      OR
      (a.type='bydept'
       AND a.constraint_data IN (
          SELECT DISTINCT
            SUBSTRING(SUBSTRING_INDEX(crs.idnumber,'-',2),5)
          FROM
            {$CFG->prefix}role_assignments AS r_asg
            JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
            JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
            JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
            JOIN {$CFG->prefix}course AS crs ON crs.id = ctx.instanceid
          WHERE
            role.name in ('student','auditor_student')
            AND ctx.contextlevel =  ". CONTEXT_COURSE."
            AND (crs.idnumber LIKE '___-____-%' OR crs.idnumber LIKE '___-___-%')
            AND usr.id = $for_user_id))
      OR
      (a.type='byinstr'
       AND a.constraint_id IN (
          SELECT DISTINCT
            usr.id
          FROM
            {$CFG->prefix}role_assignments AS r_asg
            JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
            JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
            JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
          WHERE
            role.name in ('editingteacher','teacher')
            AND ctx.contextlevel =  ". CONTEXT_COURSE."
            AND ctx.instanceid IN (
                SELECT DISTINCT
                  ctx.instanceid
                FROM
                  {$CFG->prefix}role_assignments AS r_asg
                  JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
                  JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
                  JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
                WHERE
                  role.name in ('student','auditor_student')
                  AND ctx.contextlevel =  ". CONTEXT_COURSE."
                  AND usr.id = $for_user_id
            )))
".
###############################
# NOTE: this is Williams specific, and is how access by grad year is
#  handled in our system (i.e. via Williams specific tables) - I've
#  left this code in a comment in case you want to implement a similar
#  thing on your own system.
###############################
#    "UNION
#    SELECT DISTINCT
#      a.sheet_id".
#($includeAccessRecords?'
#      ,a.id AS id
#      ,a.created_at AS created_at
#      ,a.updated_at AS updated_at
#      ,a.last_user_id AS last_user_id
#      ,a.type AS type
#      ,a.constraint_id AS constraint_id
#      ,a.constraint_data AS constraint_data
#      ,a.broadness AS broadness':'')."
#    FROM
#      {$CFG->prefix}sus_access AS a
#      JOIN wms_card_ps_users AS wcpu ON wcpu.login_id = '{$USER->username}' AND wcpu.wms_class=a.constraint_data
#    WHERE
#      a.type='bygradyear'".
###############################
") AS ac ON s.id = ac.sheet_id
WHERE
  s.flag_deleted != 1
  AND s.date_closes > ".(mktime()-86400).
//(($for_sheet_id>0 || $for_access_id>0)?"WHERE ":'').
(($for_access_id > 0)?"\n  AND ac.id = $for_access_id":'').
//(($for_sheet_id>0 && $for_access_id>0)?" AND ":'').
(($for_sheet_id > 0)?"\n  AND s.id = $for_sheet_id":'')."
ORDER BY
  s.name".
($includeAccessRecords?'
  ,ac.broadness DESC':'');

    //echo "\n<pre>\nsql is $sql</pre>\n\n";
    debug(5,"\n<pre>\nsql is $sql</pre>\n\n");
    

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an optional sheet group id
// returns: an array of sheet group objects belonging to the current user
function getSheetGroup($group_id=0)
{
    global $USER, $CFG, $COURSE, $SHEET_GROUP_FIELDS;

    $sql = "
SELECT
$SHEET_GROUP_FIELDS
FROM
  {$CFG->prefix}sus_sheetgroups AS sg
WHERE sg.flag_deleted != 1
  AND sg.owner_user_id = $USER->id
".($group_id?"  AND sg.id=$group_id":'');

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet group id, and an assoc array of info about the sheet group,
//    with any or all of (deleted, owner_user_id, flag_is_default, name, description,
//    max_total_user_signups, max_pending_user_signups)
// does: updates the sheet group of that id with the provided info
// returns: true on success, false on failure
function updateSheetGroup($sheet_group_id, $group_info)
{
    global $CFG;

    $sql = "UPDATE {$CFG->prefix}sus_sheetgroups\n SET  updated_at=".time();
    foreach ($group_info as $field=>$value)
    {
        $sql .= "\n ,$field=".quote_smart($value);
    }
    $sql .= "\n WHERE id=$sheet_group_id";

    return execute_sql($sql, false); // second param turns off direct-to-screen feedback
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet group id, and an assoc array of info about the sheet group,
//    with (name, description, max_total_user_signups, max_pending_user_signups)
// does:  sheet group of that id with the provided info
// returns: the ID of the newly added sheet group, or 0 on failure
function addSheetGroup($group_info)
{
    global $USER,$CFG;

    $group_info['created_at'] = time();
    $group_info['updated_at'] = time();
    $group_info['flag_deleted'] = 0;
    $group_info['owner_user_id'] = $USER->id;

    return insert_record('sus_sheetgroups',(object)$group_info);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet group id
// does:  marks that sheet group as deleted
// returns: true on success, false on failure
function deleteSheetGroup($group_id)
{
    $the_sheets = getOwnedSheetsAndAssociatedInfo($group_id);
    //print_r($the_sheets);
    foreach ($the_sheets as $sheet_plus) {
        if ($sheet_plus->s_id) { deleteSheet($sheet_plus->s_id); }
    }
    updateSheetGroup($group_id, array('flag_deleted' => 1));
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: and optional prefix to use with the field names
// returns: an array containing a single object, which is a sparse (sheet info only) SheetsAndAssociatedInfo item for a new sheet group
function newSheetGroup($prefix='')
{
    global $USER;

    $newgroup = array(
        $prefix.'flag_deleted' => 0,
        $prefix.'owner_user_id' => $USER->id,
        $prefix.'name' => 'New Sheet Group',
        $prefix.'description' => 'description of new sheet group',
        $prefix.'max_g_total_user_signups' => -1,
        $prefix.'max_g_pending_user_signups' => -1
        );

    return array((object)$newgroup); 
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: as sheet group id and optional prefix to use with the field names
// returns: an array containing a single object, which is a sparse (sheet info only) SheetsAndAssociatedInfo item for a new sheet group
function newSheet($group_id,$prefix='')
{
    global $USER;    

    $newsheet = array(
        $prefix.'created_at' => time(),
        $prefix.'updated_at' => time(),
        $prefix.'flag_deleted' => 0,
        $prefix.'owner_user_id' => $USER->id,
        $prefix.'last_user_id' => $USER->id,
        $prefix.'sus_sheetgroup_id' => $group_id,
        $prefix.'name' => 'New Sheet',
        $prefix.'description' => 'description and/or instructions for new sheet',
        $prefix.'type' => 'TIME',
        $prefix.'date_opens' => time(),
        $prefix.'date_closes' => time(),
        $prefix.'max_total_user_signups' => -1,
        $prefix.'max_pending_user_signups' => -1,
        $prefix.'flag_alert_owner_change' => 1,
        $prefix.'flag_alert_owner_signup' => 1,
        $prefix.'flag_alert_owner_imminent' => 1,
        $prefix.'flag_alert_admin_change' => 1,
        $prefix.'flag_alert_admin_signup' => 1,
        $prefix.'flag_alert_admin_imminent' => 1,
        $prefix.'flag_private_signups' => 1
    );

    $groups = getSheetGroup($group_id);
    $g = $groups[0];
    $newsheet['group'] = $g;
//    foreach ((array)$g as $p => $v)
//    {
//        $newsheet[$p] = $v;
//    }

    return array((object)$newsheet); 
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an assoc array of info about the sheet group,
//    with (name, description, max_total_user_signups, max_pending_user_signups)
// does:  adds a record to the DB
// returns: the ID of the newly added sheet, or 0 on failure
function addSheet($sheet_info)
{
    global $USER,$CFG;

    $sheet_info['created_at'] = time();
    $sheet_info['updated_at'] = time();
    $sheet_info['flag_deleted'] = 0;
    $sheet_info['owner_user_id'] = $USER->id;
    $sheet_info['last_user_id'] = $USER->id;

    return insert_record('sus_sheets',(object)$sheet_info,true);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id
// does:  marks that sheet as deleted
// returns: true on success, false on failure
function deleteSheet($sheet_id)
{
    updateSheet($sheet_id, array('flag_deleted' => 1));
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id, and an assoc array of info about the sheet,
//    with any or all of (deleted, owner_user_id, last_user_id, sus_sheet_group_id, name, 
//    description, type, date_opens, date_closes, max_total_user_signups,
//    max_pending_user_signups, flag_alert_owner_change, flag_alert_owner_signup,
//    flag_alert_owner_imminent, flag_alert_admin_change, flag_alert_admin_signup,
//    flag_alert_admin_imminent)
// does: updates the sheet of that id with the provided info
// returns: true on success, false on failure
function updateSheet($sheet_id, $sheet_info)
{
    global $CFG;

    $sql = "UPDATE {$CFG->prefix}sus_sheets\n SET  updated_at=".time();
    foreach ($sheet_info as $field=>$value)
    {
        $sql .= "\n ,$field=".quote_smart($value);
    }
    $sql .= "\n WHERE id=$sheet_id";

    if (execute_sql($sql, false))  // second param turns off direct-to-screen feedback
    {
        // base sheet info updated, now handle the acceess and admin control?
        // OR that's handled entirely by ajax from the sheet edit page?
        return true;
        debug_r(5,$_REQUEST);
    } else
    {
      return false;
    }
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id
// returns: a single object, which is an opening  item for a new sheet opening
function newOpening($sheet_id)
{
    global $USER;

    $newopening = array(
        'created_at' => time(),
        'updated_at' => time(),
        'flag_deleted' => 0,
        'last_user_id' => $USER->id,
        'sus_sheet_id' => $sheet_id,
        'opening_set_id' => '',
        'name' => '',
        'description' => '',
        'max_signups' => '',
        'admin_comment' => '',
        'begin_datetime' => '',
        'end_datetime' => '',
        'location' => ''
    );

    return (object)$newopening;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an opening object (as returned from newOpening)
// does:  adds that opening to the DB
// returns: the ID of the newly added opening, or <= 0 on failure
//    0 = generic failure
//    -1 = timing conflict
function addOpening($opening)
{
    global $CFG;

    // first check that there's no timing conflict
    $sql = "
SELECT
  o.id AS o_id
FROM
  {$CFG->prefix}sus_openings AS o
WHERE
  o.sus_sheet_id = {$opening->sus_sheet_id}
  AND o.flag_deleted != 1
  AND
  (
    o.begin_datetime BETWEEN {$opening->begin_datetime} AND {$opening->end_datetime}-1
    OR o.end_datetime BETWEEN {$opening->begin_datetime}+1 AND {$opening->end_datetime}
    OR {$opening->begin_datetime} BETWEEN o.begin_datetime AND o.end_datetime-1
    OR {$opening->end_datetime} BETWEEN o.begin_datetime+1 AND o.end_datetime
  )
";

//    OR o.begin_datetime = {$opening->begin_datetime}
//    OR o.end_datetime = {$opening->end_datetime}

    //echo "\n<pre>\nsql is $sql</pre>\n\n";
    debug(5,"\n<pre>\nsql is $sql</pre>\n\n");
    log_debug(5,"\n<pre>\nsql is $sql</pre>\n\n");

    $res = sus_get_records_sql($sql);

    if ((count($res) > 0) && ($res[0] != ''))
    {
        log_debug(1,"timing conflict with at least ".$res[0]->o_id);
        return -1;
    }

    return insert_record('sus_openings',$opening,true);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an id, and an assoc array of info about the opening
//    with any or all of (deleted, name, description, max_signups, 
//    admin_comment,location,begin_datetime,end_datetime)
// does: updates the opening of that id with the provided info
// returns: true on success, false on failure
function updateOpening($opening_id, $opening_info)
{
    global $CFG,$USER;

    $sql = "UPDATE {$CFG->prefix}sus_openings\n SET  updated_at=".time().",last_user_id=".$USER->id;
    foreach ($opening_info as $field=>$value)
    {
        $sql .= "\n ,$field=".quote_smart($value);
    }
    $sql .= "\n WHERE id=$opening_id";

    debug(5,$sql);
    
    if (execute_sql($sql, false)) // second param turns off direct-to-screen feedback
    {
        return true;
    } else
    {
        log_debug(1,"opening update failed on:\n$sql\nwith ".mysql_err());
        return false;
    }
}


////////////////////////////////////////////////////////////////////////////////////////

# takes: a sheet id, an optional opening_id, an optional time value (i.e. a full datetime
#  value, as returned from time() or mktime()), and an optional flag
#  for retrieving sheet info as well as opening info
# returns: an array of opening objects for that sheet. If an opening
#  id is given, returns only info for that opening. If the date is
#  given, gets only the openings on that date, otherwise all openings
#  for that sheet. If $flag_get_sheet_info is set, also gets
#  denormalized sheet data.
function getOpenings($sheet_id,$opening_id=0,$datetime=0,$flag_get_sheet_info=false)
{
    global $CFG,$OPENING_FIELDS,$SHEET_FIELDS;

    $grouper = $OPENING_FIELDS .($flag_get_sheet_info?"
,$SHEET_FIELDS":'');

    // strip the AS s_foo from the grouping clause fields
    $grouper = preg_replace("/\\s+AS \\w+(\n|$)/","\n",$grouper);

    debug(5,"grouper is $grouper");

    $sql = "
SELECT
$OPENING_FIELDS
,COUNT(su.id) AS o_num_signups".
($flag_get_sheet_info?"
,$SHEET_FIELDS":'')."
FROM
  {$CFG->prefix}sus_openings AS o
  JOIN {$CFG->prefix}sus_sheets AS s ON s.id=o.sus_sheet_id
  LEFT OUTER JOIN {$CFG->prefix}sus_signups AS su ON su.sus_opening_id = o.id
WHERE s.flag_deleted != 1
  AND s.id = $sheet_id
  AND o.flag_deleted != 1
  AND IFNULL(su.flag_deleted,0) != 1
".($datetime?"  AND FROM_UNIXTIME(o.begin_datetime,'%Y%m%d')= FROM_UNIXTIME($datetime,'%Y%m%d')\n":'')
 .($opening_id?"  AND o.id= $opening_id\n":'').
"
GROUP BY
$grouper
ORDER BY
  o.begin_datetime";

    // error("\n\nsql is $sql\n\n");
    debug(5,"\n\nsql is $sql\n\n");

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////
// takes: an opening id
// does: marks that opening as deleted 
// returns: true on success, false otherwise
function removeOpening($opening_id){
    global $CFG;
    $sql = "UPDATE {$CFG->prefix}sus_openings\n  SET updated_at=".time().",flag_deleted=1\n  WHERE id=$opening_id";
    $res = execute_sql($sql, false); // second param turns off direct-to-screen feedback
    $sql = "UPDATE {$CFG->prefix}sus_signups\n  SET updated_at=".time().",flag_deleted=1\n  WHERE sus_opening_id=$opening_id";
    return ($res && execute_sql($sql, false));
}


////////////////////////////////////////////////////////////////////////////////////////

// TODO - add flags for including opening and sheet info, and implement that of course
// takes: a signup id
// returns: the signup object for that it
function getSignup($signup_id)
{
    global $CFG,$SIGNUP_FIELDS;

    $sql="
SELECT
$SIGNUP_FIELDS
FROM
  {$CFG->prefix}sus_signups AS su
WHERE
  su.id=$signup_id";

    $res = sus_get_records_sql($sql);

    if (! $res) { return false; }

    return $res[0];
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an opening id, a user id or name
// returns: a single object, which is a blank access item for a the given sheet
function newSignup($opening_id,$user_id_or_name)
{
    global $USER;
    
    if (! preg_match('/^\\d+$/',$user_id_or_name))
    {
        global $CFG;
        $sql = "SELECT u.id AS uid FROM {$CFG->prefix}user AS u WHERE u.username = '$user_id_or_name'";
        $uids = sus_get_records_sql($sql);
        if (! $uids)
        {
            return false;
        }
        $user_id_or_name = $uids[0]->uid;
    }

    $newsignup = array(
        'created_at' => time(),
        'updated_at' => time(),
        'flag_deleted' => 0,
        'last_user_id' => $USER->id,
        'sus_opening_id' => $opening_id,
        'signup_user_id' => $user_id_or_name,
        'admin_comment' => ''
    );

    return (object)$newsignup;
}

/////////////////////////////////////////////////////////////////////////////////////////

// takes: a signup object (as returned from newSignup)
// does:  adds that signup to the DB
// returns: the ID of the newly added signup, or 0 on failure
function addSignup($signup)
{
    return insert_record('sus_signups',$signup,true);
}

/////////////////////////////////////////////////////////////////////////////////////////

// takes: a signup id
// does: marks that signup as deleted
// returns: true on success, false otherwise
function removeSignup($signup_id)
{
    global $CFG;
    $sql = "UPDATE {$CFG->prefix}sus_signups\n  SET updated_at=".time().",flag_deleted=1\n  WHERE id=$signup_id";
    return execute_sql($sql, false); // second param turns off direct-to-screen feedback
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a signup id, and an assoc array of info about the signup
//    with any or all of (flag_deleted,admin_comment)
// does: updates the signup of that id with the provided info
// returns: true on success, false on failure
function updateSignup($su_id, $su_info)
{
    global $CFG,$USER;

    $cond = '';
    $sql = "UPDATE {$CFG->prefix}sus_signups\n SET  updated_at=".time().",last_user_id=".$USER->id;
    foreach ($su_info as $field=>$value)
    {
        $sql .= "\n ,$field=".quote_smart($value);
        $cond .= " AND $field != ".quote_smart($value);
    }
    $sql .= "\n WHERE id=$su_id $cond";

    return execute_sql($sql, false); // second param turns off direct-to-screen feedback
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: a user ID, an optional from date in YYYYMMDD format, an optional to date in YYYYMMDD format
// returns: an array of signup data for that user (i.e. sign-ups of that user)
//  if from date is provided, all returned are on or after the from date
//  if to date is provided, all returned are on or before the to date
function getSignupsBySignee($user_id,$from_ymd='',$to_ymd='')
{
    global $CFG;

    // convert dates to unix timestamps for faster queries
    if ($from_ymd)
    {
        $from_ymd = mktime(0,1,1,substr($from_ymd,4,2),substr($from_ymd,6,2),substr($from_ymd,0,4));
     }
     if ($to_ymd)
     {
        $to_ymd = mktime(0,1,1,substr($to_ymd,4,2),substr($to_ymd,6,2),substr($to_ymd,0,4));
     }


    $sql = "
SELECT
  su.id AS su_id
 ,o.id AS o_id
 ,o.sus_sheet_id AS o_sus_sheet_id
 ,o.name AS o_name
 ,o.description AS o_description
 ,o.max_signups AS o_max_signups
 ,COUNT(su2.id) AS o_num_signups
 ,o.location AS o_location
 ,o.begin_datetime AS o_begin_datetime
 ,o.end_datetime AS o_end_datetime
 ,o.end_datetime - o.begin_datetime AS o_dur_seconds
 ,FROM_UNIXTIME(o.begin_datetime,'%Y%m%d') AS o_dateymd
 ,FROM_UNIXTIME(o.begin_datetime,'%Y-%m-%d') AS o_date_y_m_d
 ,FROM_UNIXTIME(o.begin_datetime,'%l:%i %p') AS o_begin_time_h_m_p
 ,FROM_UNIXTIME(o.end_datetime,'%l:%i %p') AS o_end_time_h_m_p
 ,FROM_UNIXTIME(o.begin_datetime,'%k:%i') AS o_begin_time_h24_m
 ,FROM_UNIXTIME(o.end_datetime,'%k:%i') AS o_end_time_h24_m
 ,s.id AS s_id
 ,s.name  AS s_name
 ,s.description  AS s_description
 ,s.max_total_user_signups  AS s_max_total_user_signups
 ,s.max_pending_user_signups  AS s_max_pending_user_signups
 ,sg.id AS sg_id
 ,sg.name  AS sg_name
 ,sg.description  AS sg_description
 ,sg.max_g_total_user_signups  AS sg_max_g_total_user_signups
 ,sg.max_g_pending_user_signups  AS sg_max_g_pending_user_signups
FROM
 {$CFG->prefix}sus_signups AS su
 JOIN {$CFG->prefix}sus_openings AS o ON o.id = su.sus_opening_id
 JOIN {$CFG->prefix}sus_signups AS su2 ON su2.sus_opening_id = o.id AND su2.flag_deleted != 1
 JOIN {$CFG->prefix}sus_sheets AS s ON s.id = o.sus_sheet_id
 JOIN {$CFG->prefix}sus_sheetgroups AS sg ON sg.id = s.sus_sheetgroup_id
WHERE
 su.signup_user_id = $user_id
 AND su.flag_deleted != 1
 AND o.flag_deleted != 1
 AND s.flag_deleted != 1
 AND sg.flag_deleted != 1".
($from_ymd?"\n AND o.begin_datetime >= $from_ymd":'').
($to_ymd?"\n AND o.begin_datetime <= $to_ymd":'')."
GROUP BY
  su_id
 ,o_id
 ,o_sus_sheet_id
 ,o_name
 ,o_description
 ,o_max_signups
 ,o_location
 ,o_begin_datetime
 ,o_end_datetime
 ,o_dur_seconds
 ,o_dateymd
 ,o_date_y_m_d
 ,o_begin_time_h_m_p
 ,o_end_time_h_m_p
 ,o_begin_time_h24_m
 ,o_end_time_h24_m
 ,s_id
 ,s_name
 ,s_description
 ,s_max_total_user_signups
 ,s_max_pending_user_signups
 ,sg_id
 ,sg_name
 ,sg_description
 ,sg_max_g_total_user_signups
 ,sg_max_g_pending_user_signups
ORDER BY
 o.begin_datetime
 ,o.id
 ,s.id";

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: start and end times (unix timestamp, as from mktime)
// returns: a complex data object. 
//   Primary level is as an array of user data objects
//   Each user has an field sign-ups which has and array of signup objects, ordered by the begin time of their associated opening
//   Each signup has an opening field, which holds an opening object
//   Each opening has a sheet field, which holds a sheet object
//   Each sheet has a sheet_group field, which holds a sheet_group object
//  NOTE- be careful with this function, it can potentially return a denormalized set of ALL sign-ups and associated data if range is large enough
function getStructuredUserSignups($begin_timestamp,$end_timestamp)
{
    global $CFG,$USER_FIELDS,$SIGNUP_FIELDS,$OPENING_FIELDS,$SHEET_FIELDS,$SHEET_GROUP_FIELDS;

    if (! ($begin_timestamp && $end_timestamp))
    {
        error("begin and end timestamps required for getStructuredUserSignups");
        exit;
    }

    $sql = "
SELECT
$USER_FIELDS
,$SIGNUP_FIELDS
,$OPENING_FIELDS
,$SHEET_FIELDS
,$SHEET_GROUP_FIELDS
FROM
  {$CFG->prefix}user AS usr
  JOIN {$CFG->prefix}sus_signups AS su ON su.signup_user_id = usr.id AND su.flag_deleted != 1
  JOIN {$CFG->prefix}sus_openings AS o ON o.id = su.sus_opening_id AND o.flag_deleted != 1
  JOIN {$CFG->prefix}sus_sheets AS s ON s.id = o.sus_sheet_id AND s.flag_deleted != 1
  JOIN {$CFG->prefix}sus_sheetgroups AS sg ON sg.id = s.sus_sheetgroup_id AND sg.flag_deleted != 1
WHERE
  o.begin_datetime BETWEEN $begin_timestamp AND $end_timestamp
ORDER BY
  usr.id,
  o.begin_datetime";

    //echo "$sql\n\n";

    $res = sus_get_records_sql($sql);
    if (! $res)
    {
        return false;
    }

    $structured_data = array();
    $last_user = '';
    $user_data = '';

    //print_r($res); exit; // DEBUG/DEV

    foreach ($res as $r)
    {
        $r = (array)$r; // convert the object to an array so we can dynamically traverse its fields

        if ($r['id'] != $last_user)
        {
            // switching to a new user, so finish up the last one, if it exists
            if ($last_user != '')
            {
                $structured_data[] = $user_data;
            }

            // set up new user
            $user_data = (object)array(
                                       'id'=>$r['id']
                                     ,'auth'=>$r['auth']
                                     ,'confirmed'=>$r['confirmed']
                                     ,'policyagreed'=>$r['policyagreed']
                                     ,'deleted'=>$r['deleted']
                                     ,'mnethostid'=>$r['mnethostid']
                                     ,'username'=>$r['username']
                                     ,'password'=>$r['password']
                                     ,'idnumber'=>$r['idnumber']
                                     ,'firstname'=>$r['firstname']
                                     ,'lastname'=>$r['lastname']
                                     ,'email'=>$r['email']
                                     ,'emailstop'=>$r['emailstop']
                                     ,'mailformat'=>$r['mailformat']
                                     ,'maildigest'=>$r['maildigest']
                                     ,'maildisplay'=>$r['maildisplay']
                                     ,'signups' => array()
                                      );

            $last_user = $r['id'];
        } // end if ($r->usr_id != $last_user)

       // print_r($user_data); exit; // DEBUG/DEV

        // sort through the data fields, populating approp array as needed
        $su_data = array();
        $o_data = array();
        $s_data = array();
        $sg_data = array();

        foreach (array_keys($r) as $rk)
        {
            // NOTE: need to use === because strpos returns false (which is == 0) when needle is not found
            if (strpos($rk,'su_') === 0)
            {
                $su_data[$rk] = $r[$rk];
            } else if (strpos($rk,'o_') === 0)
            {
                $o_data[$rk] = $r[$rk];
            } else if (strpos($rk,'s_') === 0)
            {
                $s_data[$rk] = $r[$rk];
            } else if (strpos($rk,'sg_') === 0)
            {
                $sg_data[$rk] = $r[$rk];
            }
        }

        $s_data['sheet_group'] = (object)$sg_data;
        $o_data['sheet'] = (object)$s_data;
        $su_data['opening'] = (object)$o_data;

        $user_data->signups[] = (object)$su_data;

    } // end foreach ($res as $r)

    // don't forget that last bunch of accumulated data!
    $structured_data[] = $user_data;

    return $structured_data;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: start and end times (unix timestamp, as from mktime)
// does: getssign-upsfor sheets for which the user is the owner or admin
// returns: a complex data object.
//   Primary level is as an array of user data objects
//   Each user has an field openings which has and array of openings objects, ordered by begin time
//   Each opening has sheet field which holds a sheet object, and a sign-ups field which holds an array of signup objects
//   Each signup has an susr field which has an susr object
//   Each sheet has a sheet_group field, which holds a sheet_group object
//  NOTE- be careful with this function, it can potentially return a denormalized set of ALL sign-ups and associated data if range is large enough
//  NOTE- this function is kind of the inverse of getStructuredUserSignups
function getStructuredSignupsForUsers($begin_timestamp,$end_timestamp)
{
    global $CFG,$USER_FIELDS,$SIGNUP_FIELDS,$OPENING_FIELDS,$SHEET_FIELDS;

    $sql = "
SELECT
$USER_FIELDS
,$SIGNUP_FIELDS
,$OPENING_FIELDS
,$SHEET_FIELDS
,'owned' AS s_for_me_type
,susr.id AS susr_id
,susr.username AS susr_username
,susr.firstname AS susr_firstname
,susr.lastname AS susr_lastname
,susr.email AS susr_email
FROM
  {$CFG->prefix}user AS usr
  JOIN {$CFG->prefix}sus_sheets AS s ON s.owner_user_id = usr.id AND s.flag_deleted != 1
  JOIN {$CFG->prefix}sus_openings AS o ON o.sus_sheet_id = s.id AND o.flag_deleted != 1
  JOIN {$CFG->prefix}sus_signups AS su ON su.sus_opening_id = o.id AND su.flag_deleted != 1
  JOIN {$CFG->prefix}user AS susr ON susr.id = su.signup_user_id 
WHERE
  o.begin_datetime BETWEEN $begin_timestamp AND $end_timestamp
UNION
SELECT
$USER_FIELDS
,$SIGNUP_FIELDS
,$OPENING_FIELDS
,$SHEET_FIELDS
,'adminned' AS s_for_me_type
,susr.id AS susr_id
,susr.username AS susr_username
,susr.firstname AS susr_firstname
,susr.lastname AS susr_lastname
,susr.email AS susr_email
FROM
  {$CFG->prefix}user AS usr
  JOIN {$CFG->prefix}sus_access AS a ON a.constraint_data = usr.username AND a.type = 'adminbyuser'
  JOIN {$CFG->prefix}sus_sheets AS s ON s.id = a.sheet_id AND s.flag_deleted != 1
  JOIN {$CFG->prefix}sus_openings AS o ON o.sus_sheet_id = s.id AND o.flag_deleted != 1
  JOIN {$CFG->prefix}sus_signups AS su ON su.sus_opening_id = o.id AND su.flag_deleted != 1
  JOIN {$CFG->prefix}user AS susr ON susr.id = su.signup_user_id
WHERE
  o.begin_datetime BETWEEN $begin_timestamp AND $end_timestamp
ORDER BY
  1
 ,o_begin_datetime
 ,o_id";

    //echo "$sql\n\n";

    $res = sus_get_records_sql($sql);
    if (! $res)
    {
        return false;
    }

    $structured_data = array();
    $last_user = '';
    $user_data = '';
    $last_opening = '';
    $usr_opening_idx = -1;

    foreach ($res as $r)
    {
        $r = (array)$r; // convert the object to an array so we can dynamically traverse its fields

        if ($r['id'] != $last_user)
        {
            // switching to a new user, so finish up the last one, if it exists
            if ($last_user != '')
            {
                $structured_data[] = $user_data;
            }

            // set up new user
            $user_data = (object)array(
                                       'id'=>$r['id']
                                     ,'auth'=>$r['auth']
                                     ,'confirmed'=>$r['confirmed']
                                     ,'policyagreed'=>$r['policyagreed']
                                     ,'deleted'=>$r['deleted']
                                     ,'mnethostid'=>$r['mnethostid']
                                     ,'username'=>$r['username']
                                     ,'password'=>$r['password']
                                     ,'idnumber'=>$r['idnumber']
                                     ,'firstname'=>$r['firstname']
                                     ,'lastname'=>$r['lastname']
                                     ,'email'=>$r['email']
                                     ,'emailstop'=>$r['emailstop']
                                     ,'mailformat'=>$r['mailformat']
                                     ,'maildigest'=>$r['maildigest']
                                     ,'maildisplay'=>$r['maildisplay']
                                     ,'openings' => array()
                                      );

            $last_user = $r['id'];
            $last_opening = ''; // reset opening tracking for the new user
            $usr_opening_idx = -1;
        } // end if ($r->usr_id != $last_user)

       // print_r($user_data); exit; // DEBUG/DEV

        // sort through the data fields, populating approp array as needed
        $su_data = array();
        $susr_data = array();
        $o_data = array();
        $s_data = array();
        $sg_data = array();
        foreach (array_keys($r) as $rk)
        {
            // NOTE: need to use === because strpos returns false (which is == 0) when needle is not found
            if (strpos($rk,'su_') === 0)
            {
                $su_data[$rk] = $r[$rk];
            } else if (strpos($rk,'susr_') === 0)
            {
                $susr_data[$rk] = $r[$rk];
            } else if (strpos($rk,'o_') === 0)
            {
                $o_data[$rk] = $r[$rk];
            } else if (strpos($rk,'s_') === 0)
            {
                $s_data[$rk] = $r[$rk];
            } else if (strpos($rk,'sg_') === 0)
            {
                $sg_data[$rk] = $r[$rk];
            }
        }

        // structure and place/remember the data
        if ($o_data['o_id'] != $last_opening) // create new opening if needed
        {
            $last_opening = $o_data['o_id'];

            $s_data['sheet_group'] = (object)$sg_data;
            $o_data['sheet'] = (object)$s_data;
            $o_data['signups'] = array();

            $usr_opening_idx++;
            $user_data->openings[$usr_opening_idx] = (object)$o_data;
        }

        $su_data['susr'] = (object)$susr_data;
        $user_data->openings[$usr_opening_idx]->signups[] = (object)$su_data;

    } // end foreach ($res as $r)

    // don't forget that last bunch of accumulated data!
    $structured_data[] = $user_data;

    return $structured_data;
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: an array of sign-ups info (as returned by
//   getSignupsBySignee), and an optional flag for displaying the results
//   directly (vs returning them as a string) - defaults to true
//   (i.e. display directly by default)
// does: prints the HTML for the formatted list (<ul>) for those signups
// returns: none, or the HTML
function generateMySignupsList($mySignupsComplex,$flag_direct_display=true)
{
    global $USER,$CFG;
    $now = ymd();

    $html = '';

    $prior_dateymd = '';
    $html .= "<ul class=\"my_signups_list\">\n";
    $class_chrono = '';
    foreach ($mySignupsComplex as $su)
    {
        if ($su->o_dateymd != $prior_dateymd)
        {
          if ($prior_dateymd != '')
          {
              $html .= "    </ul>\n  </li>\n";
          }
          if ($su->o_dateymd < $now)
          {
              $class_chrono = ' my_in_past';
          } else if ($su->o_dateymd == $now)
          {
              $class_chrono = ' my_today';
          } else
          {
              $class_chrono = '';
          }
          $html .= "  <li class=\"signups_on_{$su->o_dateymd}$class_chrono\"><span class=\"opening_day\">{$su->o_date_y_m_d}</span>\n    <ul class=\"list_signups_on_{$su->o_dateymd}\">\n";
          $prior_dateymd = $su->o_dateymd;
        }
        $html .= "      <li class=\"signup_{$su->su_id}\">\n";
        if (! $class_chrono) // can't delete past signups
        {
            $html .= "        <img class=\"remove_signup_link nukeit\" src=\"image/pix/t/delete.png\" "
                           ."alt=\"remove signup\" title=\"remove signup\" for_signup=\"{$su->su_id}\" for_opening=\"{$su->o_id}\" for_sheet=\"{$su->s_id}\"/>\n";
        }
        $capacity_text = $su->o_max_signups;
        if ($capacity_text < 1)
        {
            $capacity_text = '*';
        }
        $html .= "        <span class=\"opening_time_range\">{$su->o_begin_time_h_m_p} - {$su->o_end_time_h_m_p}</span>\n";
        $html .= "        <span class=\"opening_capacity\">({$su->o_num_signups}/$capacity_text)</span>\n";
        if ($su->o_location)
        {
            $html .= "        <span class=\"opening_location\">@ {$su->o_location}</span>\n";
        }
        $html .= "        for <span class=\"sheet_name\" for_sheet=\"{$su->s_id}\" for_opening=\"{$su->o_id}\">{$su->s_name}</span>"
                      ."<div class=\"sheet_details\" id=\"sheet_details_for_{$su->s_id}_{$su->o_id}\"><div class=\"sus_very_small\">";
        if ($su->s_max_total_user_signups > 0)
        {
            $html .= "You're signed up for ".$USER->signups['total']['sheets'][$su->s_id]."/{$su->s_max_total_user_signups} total on this sheet. ";
        }
        if ($su->s_max_pending_user_signups>0)
        {
            $html .= "(and ".($USER->signups['future']['sheets'][$su->s_id]+0)."/{$su->s_max_pending_user_signups} future on this sheet) ";
        }

        if ($su->sg_max_g_total_user_signups > 0)
        {
            $html .= "You're signed up for ".$USER->signups['total']['groups'][$su->sg_id]."/{$su->sg_max_g_total_user_signups} total in this group. ";
        }
        if ($su->sg_max_g_pending_user_signups>0)
        {
            $html .= "(and ".($USER->signups['future']['groups'][$su->sg_id]+0)."/{$su->sg_max_g_pending_user_signups} future in this group) ";
        }

        $html .= "</div>"."{$su->s_description}</div>"
                      .($su->o_name?"<span class=\"opening_name\" for_opening=\"{$su->o_id}\">, {$su->o_name}</span>":'')
                      ."\n";
        if ($su->o_description)
        {
            $html .= "        <div class=\"opening_description\">{$su->o_description}</div>\n";
        }
        $html .= "      </li>\n";
    }
    $html .= "    </ul>\n  </li>\n";
    $html .= "</ul>\n";

    if ($flag_direct_display)
    {
        echo $html;
        return '';
    } else
    {
        return $html;
    }
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a user ID, the username for that user, an optional from date in YYYYMMDD format, an optional to date in YYYYMMDD format
// returns: an array of signup data for all sign-ups on sheets owned or admin-ed by that user
//  if from date is provided, all returned are on or after the from date
//  if to date is provided, all returned are on or before the to date
function getSignupsForSheetsOf($user_id,$username,$from_ymd='',$to_ymd='')
{
    global $CFG;

    // convert dates to unix timestamps for faster queries
    if ($from_ymd)
    {
        $from_ymd = mktime(0,1,1,substr($from_ymd,4,2),substr($from_ymd,6,2),substr($from_ymd,0,4));
     }
     if ($to_ymd)
     {
        $to_ymd = mktime(0,1,1,substr($to_ymd,4,2),substr($to_ymd,6,2),substr($to_ymd,0,4));
     }

    $sql = "
SELECT
  su.id AS su_id
 ,su.signup_user_id AS su_signup_user_id
 ,su.created_at AS su_created_at
 ,su.updated_at AS su_updated_at
 ,su.admin_comment AS su_admin_comment
 ,upd_usr.id AS upd_usr_id
 ,upd_usr.username AS upd_usr_username
 ,upd_usr.firstname AS upd_usr_firstname
 ,upd_usr.lastname AS upd_usr_lastname
 ,upd_usr.email AS upd_usr_email
 ,usr.id AS usr_id
 ,usr.username AS usr_username
 ,usr.firstname AS usr_firstname
 ,usr.lastname AS usr_lastname
 ,usr.email AS usr_email
 ,o.id AS o_id
 ,o.name AS o_name
 ,o.description AS o_description
 ,o.max_signups AS o_max_signups
 ,COUNT(su2.id) AS o_num_signups
 ,o.location AS o_location
 ,o.begin_datetime AS o_begin_datetime
 ,o.end_datetime AS o_end_datetime
 ,o.end_datetime - o.begin_datetime AS o_dur_seconds
 ,FROM_UNIXTIME(o.begin_datetime,'%Y%m%d') AS o_dateymd
 ,FROM_UNIXTIME(o.begin_datetime,'%Y-%m-%d') AS o_date_y_m_d
 ,FROM_UNIXTIME(o.begin_datetime,'%l:%i %p') AS o_begin_time_h_m_p
 ,FROM_UNIXTIME(o.end_datetime,'%l:%i %p') AS o_end_time_h_m_p
 ,FROM_UNIXTIME(o.begin_datetime,'%k:%i') AS o_begin_time_h24_m
 ,FROM_UNIXTIME(o.end_datetime,'%k:%i') AS o_end_time_h24_m
 ,s.id AS s_id
 ,s.name  AS s_name
 ,s.description  AS s_description
 ,s.max_total_user_signups  AS s_max_total_user_signups
 ,s.max_pending_user_signups  AS s_max_pending_user_signups
 ,own_usr.id AS own_usr_id
 ,own_usr.username AS own_usr_username
 ,own_usr.firstname AS own_usr_firstname
 ,own_usr.lastname AS own_usr_lastname
 ,own_usr.email AS own_usr_email
 ,sg.id AS sg_id
 ,sg.name  AS sg_name
 ,sg.description  AS sg_description
 ,sg.max_g_total_user_signups  AS sg_max_g_total_user_signups
 ,sg.max_g_pending_user_signups  AS sg_max_g_pending_user_signups
FROM
 {$CFG->prefix}sus_signups AS su
 JOIN {$CFG->prefix}user AS usr ON usr.id = su.signup_user_id
 JOIN {$CFG->prefix}user AS upd_usr ON upd_usr.id = su.last_user_id
 JOIN {$CFG->prefix}sus_openings AS o ON o.id = su.sus_opening_id
 JOIN {$CFG->prefix}sus_signups AS su2 ON su2.sus_opening_id = o.id AND su2.flag_deleted != 1
 JOIN {$CFG->prefix}sus_sheets AS s ON s.id = o.sus_sheet_id
 JOIN {$CFG->prefix}user AS own_usr ON own_usr.id = s.owner_user_id
 JOIN {$CFG->prefix}sus_sheetgroups AS sg ON sg.id = s.sus_sheetgroup_id
 LEFT OUTER JOIN {$CFG->prefix}sus_access ac ON ac.sheet_id = s.id AND ac.type='adminbyuser'
WHERE
 (s.owner_user_id=$user_id OR ac.constraint_data='$username')
 AND su.flag_deleted != 1
 AND o.flag_deleted != 1
 AND s.flag_deleted != 1
 AND sg.flag_deleted != 1".
($from_ymd?"\n AND o.begin_datetime >= $from_ymd":'').
($to_ymd?"\n AND o.begin_datetime <= $to_ymd":'')."
GROUP BY
  su_id
 ,su_signup_user_id
 ,su_created_at
 ,su_updated_at
 ,su_admin_comment
 ,upd_usr_id
 ,upd_usr_username
 ,upd_usr_firstname
 ,upd_usr_lastname
 ,upd_usr_email
 ,usr_id
 ,usr_username
 ,usr_firstname
 ,usr_lastname
 ,usr_email
 ,o_id
 ,o_name
 ,o_description
 ,o_max_signups
 ,o_location
 ,o_begin_datetime
 ,o_end_datetime
 ,o_dur_seconds
 ,o_dateymd
 ,o_date_y_m_d
 ,o_begin_time_h_m_p
 ,o_end_time_h_m_p
 ,o_begin_time_h24_m
 ,o_end_time_h24_m
 ,s_id
 ,s_name
 ,s_description
 ,s_max_total_user_signups
 ,s_max_pending_user_signups
 ,sg_id
 ,sg_name
 ,sg_description
 ,sg_max_g_total_user_signups
 ,sg_max_g_pending_user_signups
ORDER BY
 o.begin_datetime
 ,o.id
 ,s.id
 ,su.created_at";

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////
// takes: an array of sign-ups info, as returned by
//   getSignupsForSheetsOf), and an optional flag for displaying the results
//   directly (vs returning them as a string) - defaults to true
//   (i.e. display directly by default)
// does: prints the HTML for the formatted list (<ul>) for those signups
// returns: none
function generateSignupsForMeList($signupsForMeComplex,$flag_direct_display=true)
{
    global $CFG,$edit_sheet_url, $USER;
    $now = ymd();

    $html = '';

    $prior_dateymd = '';
    $prior_opening = '';
    $html .= "<ul class=\"my_signups_list\">\n";
    $class_chrono = '';
    foreach ($signupsForMeComplex as $su)
    {
        if ($su->o_dateymd != $prior_dateymd)
        {
          if ($prior_dateymd != '')
          {
              //$html .= "    </ul>\n  </li>\n";
              $html .= "        </ul>\n      </li>\n    </ul>\n  </li>\n";
          }
          if ($su->o_dateymd < $now)
          {
              $class_chrono = ' for_me_in_past';
          } else if ($su->o_dateymd == $now)
          {
              $class_chrono = ' for_me_today';
           } else
          {
              $class_chrono = '';
          }
          $html .= "  <li class=\"signups_on_{$su->o_dateymd}$class_chrono\"><span class=\"opening_day\">{$su->o_date_y_m_d}</span>\n    <ul class=\"list_signups_on_{$su->o_dateymd}\">\n";
          $prior_dateymd = $su->o_dateymd;
          $prior_opening = '';
        }

        if ($su->o_id != $prior_opening)
        {
            if ($prior_opening != '')
            {
                $html .= "        </ul>\n      </li>\n";
            }
            $prior_opening = $su->o_id;
            $capacity_text = $su->o_max_signups;
            if ($capacity_text < 1)
            {
                $capacity_text = '*';
            }
            $html .= "      <li class=\"opening_{$su->o_id}\">\n";
            $html .= "        <span class=\"opening_time_range\">{$su->o_begin_time_h_m_p} - {$su->o_end_time_h_m_p}</span>\n";
            $html .= "        <span class=\"opening_capacity\">({$su->o_num_signups}/$capacity_text)</span>\n";
            if ($su->o_location)
            {
                $html .= "        <span class=\"opening_location\">@ {$su->o_location}</span>\n";
            }
            if (! (isset($edit_sheet_url) && $edit_sheet_url))
            {
                global $COURSE;
                $currentcontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		$contextid = $currentcontext->id;
                $edit_sheet_url = $CFG->wwwroot.'/blocks/signup_sheets/?contextid='.$contextid."&action=editsheet&sheet=";
            }
            $html .= "        for <a href=\"$edit_sheet_url{$su->s_id}&sheetgroup={$su->sg_id}&opening={$su->o_id}\" class=\"sheet_name\" for_sheet=\"{$su->s_id}\" for_opening=\"{$su->o_id}\">{$su->s_name}</a>";
            if ($su->own_usr_id != $USER->id)
            {
                $html .= " (owned by {$su->own_usr_firstname} {$su->own_usr_lastname})";
            }
            $html .= "<div class=\"sheet_details\" id=\"for_sheet_details_for_{$su->s_id}_{$su->o_id}\">{$su->s_description}</div>"
                          .($su->o_name?"<span class=\"opening_name\" for_opening=\"{$su->o_id}\">, {$su->o_name}</span>":'')
                          ."\n";
            if ($su->o_description)
            {
                $html .= "        <div class=\"opening_description\">{$su->o_description}</div>\n";
            }
            $html .= "        <ul>\n";
        }

        $html .= "          <li class=\"signup_item signup_{$su->su_id}\" for_signup=\"{$su->su_id}\">";
        // admins and owners CAN delete past signups
        //if (! $class_chrono) // can't delete past signups
        //{
            $html .= "        <img class=\"remove_signup_link nukeit\" src=\"image/pix/t/delete.png\" "
                           ."alt=\"remove signup\" title=\"remove signup\" for_signup=\"{$su->su_id}\" for_opening=\"{$su->o_id}\"  for_sheet=\"{$su->s_id}\"/>\n";
        //}
        $html .= "<span class=\"sus_user_fullname\" for_signup=\"{$su->su_id}\">{$su->usr_firstname} {$su->usr_lastname}</span>";
        $html .= "<div class=\"signup_detail_info signup_detail_info_{$su->su_id}\" for_signup=\"{$su->su_id}\">";
        $html .= "  <div>Username: {$su->usr_username}</div>\n";
        $html .= "  <div>Email: {$su->usr_email}</div>\n";
        $html .= "  <div>Signed up: ".ymd_hm_a($su->su_created_at,'-')."</div>\n";
        if ($su->su_admin_comment)
        {
            $html .= "  <div class=\"admin_comment\">Admin Comment: {$su->su_admin_comment}</div>\n";
        }
        if ($su->su_created_at != $su->su_updated_at)
        {
            $html .= "  <div>Signup last changed at ".ymd_hm_a($su->su_updated_at,'-')." by {$su->upd_usr_firstname} {$su->upd_usr_lastname} ({$su->upd_usr_username} - {$su->upd_usr_email})</div>\n";
        }
        $html .= "</div>\n";
        $html .= "</li>\n";

    }
    $html .= "        </ul>\n      </li>\n";
    $html .= "    </ul>\n  </li>\n";
    $html .= "</ul>\n";

    if ($flag_direct_display)
    {
        echo $html;
        return '';
    } else
    {
        return $html;
    }
}

////////////////////////////////////////////////////////////////////////////////////////

# takes: a sheet id and an optional time value (i.e. a full datetime
#  value, as returned from time() or mktime()), an optional
#  opening_id, and an optional signup id 
# returns: a structured data object for that sheet. At the top level
#  is the sheet info as an object. In addition to the basic sheet data,
#  it has 
#      group : a signup sheet group object
#      access_controls : an complex structure of access objects (from getAccessPermissions($sheet_id))
#      openings, a time-ordered array of opening objects. 
#        Each opening object has all the opening info, and
#             signups : a time-of-signup ordered array of signups
#                user : a limited info user object
#            signups_by_id : an assoc array of sign-ups keyed by signup ID
#            signups_by_user : an assoc array of sign-ups keyed by ID of signed up user
function getStructuredSheetData($sheet_id,$datetime=0,$opening_id=0,$signup_id=0)
{
    global $CFG,$SHEET_GROUP_FIELDS,$SHEET_FIELDS,$OPENING_FIELDS,$SIGNUP_FIELDS;

    $sql = "
SELECT
$SHEET_GROUP_FIELDS
,$SHEET_FIELDS
,$OPENING_FIELDS
,$SIGNUP_FIELDS
,usr.id AS usr_id
,usr.username AS usr_username
,usr.firstname AS usr_firstname
,usr.lastname AS usr_lastname
,usr.email AS usr_email
FROM
  {$CFG->prefix}sus_sheets AS s 
  JOIN {$CFG->prefix}sus_sheetgroups AS sg ON s.sus_sheetgroup_id = sg.id
  LEFT OUTER JOIN {$CFG->prefix}sus_openings AS o ON o.sus_sheet_id=s.id AND o.flag_deleted != 1
  LEFT OUTER JOIN {$CFG->prefix}sus_signups AS su ON su.sus_opening_id = o.id AND su.flag_deleted != 1
  LEFT OUTER JOIN {$CFG->prefix}user AS usr ON usr.id = su.signup_user_id
WHERE s.flag_deleted != 1
  AND s.id = $sheet_id
".
($datetime?"  AND FROM_UNIXTIME(o.begin_datetime,'%Y%m%d')= FROM_UNIXTIME($datetime,'%Y%m%d')\n":'').
($opening_id?"  AND o.id=$opening_id\n":'').
($signup_id?"  AND su.id=$signup_id\n":'').
"ORDER BY
  o.begin_datetime,usr.lastname,usr.username,su.created_at";

    debug(5,"\n\n<pre>getStructuredSheetData sql is $sql</pre>\n\n");

    $all_rec_objs = sus_get_records_sql($sql);

    if (! $all_rec_objs)
    {
        return false;
    }

    $sheet_data = (object)(array(
      's_id' => $all_rec_objs[0]->s_id,
      's_created_at' => $all_rec_objs[0]->s_created_at,
      's_updated_at' => $all_rec_objs[0]->s_updated_at,
      's_flag_deleted' => $all_rec_objs[0]->s_flag_deleted,
      's_owner_user_id' => $all_rec_objs[0]->s_owner_user_id,
      's_last_user_id' => $all_rec_objs[0]->s_last_user_id,
      's_sus_sheetgroup_id' => $all_rec_objs[0]->s_sus_sheetgroup_id,
      's_name' => $all_rec_objs[0]->s_name,
      's_description' => $all_rec_objs[0]->s_description,
      's_type' => $all_rec_objs[0]->s_type,
      's_date_opens' => $all_rec_objs[0]->s_date_opens,
      's_date_closes' => $all_rec_objs[0]->s_date_closes,
      's_max_total_user_signups' => $all_rec_objs[0]->s_max_total_user_signups,
      's_max_pending_user_signups' => $all_rec_objs[0]->s_max_pending_user_signups,
      's_flag_alert_owner_change' => $all_rec_objs[0]->s_flag_alert_owner_change,
      's_flag_alert_owner_signup' => $all_rec_objs[0]->s_flag_alert_owner_signup,
      's_flag_alert_owner_imminent' => $all_rec_objs[0]->s_flag_alert_owner_imminent,
      's_flag_alert_admin_change' => $all_rec_objs[0]->s_flag_alert_admin_change,
      's_flag_alert_admin_signup' => $all_rec_objs[0]->s_flag_alert_admin_signup,
      's_flag_alert_admin_imminent' => $all_rec_objs[0]->s_flag_alert_admin_imminent,
      's_flag_private_signups' => $all_rec_objs[0]->s_flag_private_signups
    ));

    $sheet_data->group = (object)(array(
      'sg_id' => $all_rec_objs[0]->sg_id,
      'sg_created_at' => $all_rec_objs[0]->sg_created_at,
      'sg_updated_at' => $all_rec_objs[0]->sg_updated_at,
      'sg_owner_user_id' => $all_rec_objs[0]->sg_owner_user_id,
      'sg_flag_is_default' => $all_rec_objs[0]->sg_flag_is_default,
      'sg_name' => $all_rec_objs[0]->sg_name,
      'sg_description' => $all_rec_objs[0]->sg_description,
      'sg_max_g_total_user_signups' => $all_rec_objs[0]->sg_max_g_total_user_signups,
      'sg_max_g_pending_user_signups' => $all_rec_objs[0]->sg_max_g_pending_user_signups
    ));

    $sheet_data->access_controls = getAccessPermissions($sheet_id);
    
    $sheet_data->openings = array();

    $prior_opening_id = '';
    foreach ($all_rec_objs as $obj)
    {
      if ($prior_opening_id != $obj->o_id)
      {
        // close out accumulation
        if ($prior_opening_id)
        {
            $opening->o_num_signups = count($opening->signups);
            $sheet_data->openings[] = $opening;
        }

        // set up for next bunch
        $opening = (object)(array(
          'o_id' => $obj->o_id,
          'o_created_at' => $obj->o_created_at,
          'o_updated_at' => $obj->o_updated_at,
          'o_flag_deleted' => $obj->o_flag_deleted,
          'o_last_user_id' => $obj->o_last_user_id,
          'o_sus_sheet_id' => $obj->o_sus_sheet_id,
          'o_opening_set_id' => $obj->o_opening_set_id,
          'o_name' => $obj->o_name,
          'o_description' => $obj->o_description,
          'o_max_signups' => $obj->o_max_signups,
          'o_admin_comment' => $obj->o_admin_comment,
          'o_begin_datetime' => $obj->o_begin_datetime,
          'o_end_datetime' => $obj->o_end_datetime,
          'o_dur_seconds' => $obj->o_dur_seconds,
          'o_dateymd' => $obj->o_dateymd,
          'o_date_y_m_d' => $obj->o_date_y_m_d,
          'o_begin_time_h_m_p' => $obj->o_begin_time_h_m_p,
          'o_end_time_h_m_p' => $obj->o_end_time_h_m_p,
          'o_begin_time_h24_m' => $obj->o_begin_time_h24_m,
          'o_end_time_h24_m' => $obj->o_end_time_h24_m,
          'o_location' => $obj->o_location
        ));
#            signups : a time-of-signup ordered array of signups
#            signups_by_id : an assoc array of signups keyed by signup ID
#            signups_by_user : an assoc array of signups keyed by ID of signed up user
        $opening->signups = array();
        $opening->signups_by_id = array();
        $opening->signups_by_user = array();
        $prior_opening_id =  $obj->o_id;
      }
      if ($obj->su_id)
      {
        $user = (object)(array(
          'usr_id' => $obj->usr_id,
          'usr_username' => $obj->usr_username,
          'usr_firstname' => $obj->usr_firstname,
          'usr_lastname' => $obj->usr_lastname,
          'usr_email' => $obj->usr_email
        ));
        $su = (object)(array(
          'su_id' => $obj->su_id,
          'su_created_at' => $obj->su_created_at,
          'su_updated_at' => $obj->su_updated_at,
          'su_flag_deleted' => $obj->su_flag_deleted,
          'su_last_user_id' => $obj->su_last_user_id,
          'su_sus_opening_id' => $obj->su_sus_opening_id,
          'su_signup_user_id' => $obj->su_signup_user_id,
          'su_admin_comment' => $obj->su_admin_comment,
          'user' => $user
        ));
        $opening->signups[] = $su;
        $opening->signups_by_id["{$obj->su_id}"] = $su;
        $opening->signups_by_user["{$obj->su_signup_user_id}"] = $su;
      }
    }
    // don't forget to add that last accumulated info!
    $sheet_data->openings[] = $opening;

    //echo '<pre>';
    //print_r($sheet_data);
    //echo '</pre>';
    //exit;

    return $sheet_data;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id
// returns: an array of user data objects for the sheet owner and all users who have admin privs for it as well
function getSheetOwnerAndAdmins($sheet_id)
{
    global $CFG,$USER_FIELDS;

    $sql = "
SELECT DISTINCT
$USER_FIELDS
FROM
  {$CFG->prefix}user AS usr 
  JOIN {$CFG->prefix}sus_sheets AS s ON s.owner_user_id = usr.id
WHERE
  s.id = $sheet_id
UNION
SELECT
$USER_FIELDS
FROM
  {$CFG->prefix}user AS usr
  JOIN {$CFG->prefix}sus_access AS a ON a.constraint_data = usr.username AND a.type='adminbyuser'
  JOIN {$CFG->prefix}sus_sheets AS s ON s.id = a.sheet_id
WHERE
  s.id = $sheet_id
";

    debug(4,"\n\nsql is $sql\n\n");

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: none
// returns: an array of distinct user objects, where each user has the editingteacher or teacher role in at least one course
function getAllTeachers()
{
    global $CFG,$USER_FIELDS;

    $sql = "
SELECT DISTINCT
$USER_FIELDS
FROM
  {$CFG->prefix}role_assignments AS r_asg
  JOIN {$CFG->prefix}role AS role ON role.id = r_asg.roleid
  JOIN {$CFG->prefix}user AS usr ON usr.id = r_asg.userid
  JOIN {$CFG->prefix}context AS ctx ON ctx.id = r_asg.contextid
WHERE
  role.name in ('editingteacher','teacher')
  AND ctx.contextlevel = ". CONTEXT_COURSE."
ORDER BY
  usr.lastname
 ,usr.firstname
 ,usr.username";
  
  // CSW NOTE: CONTEXT_COURSE is a constant defined in .../lib/accesslib.php and is 50


 //   error("\n\nsql is $sql\n\n");

    return sus_get_records_sql($sql);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: none
// returns: an array of department abbreviations (e.g. ARTH, CSCI, HIST, etc.)

// NOTE: this is a Williams-specific function - it relies on the
// format we use for course ids (YYT-DEPTCCC-SS, where YY is the 2-digit
// academic year, T is a term signifier (F for fall, W for winter term,
// and S for spring), DEPT is the 3-4 letter department code (e.g. ENGL
// for the English department), CCC is a 3-digit course code (e..g 101),
// and SS is a section number (e.g. 01)

function getAllDepartments()
{
    global $CFG;

    $sql = "
SELECT DISTINCT
  SUBSTRING(c.idnumber,5,LOCATE('-',c.idnumber,5)-5) AS dept
FROM
  {$CFG->prefix}course AS c
WHERE
  c.category=5
  AND c.visible=1
  AND ((c.idnumber LIKE '___-____-___-__') OR (c.idnumber LIKE '___-___-___-__'))
ORDER BY
  dept";
    // NOTE: the patterns match 3 and 4 letter depts (e.g. 10S-MATH-101-01 and 10S-ART-100-01)

    $dept_objs = sus_get_records_sql($sql);

    // convert array of objects to array of strings    
    $dept_ar = array();
    foreach ($dept_objs as $dept_obj)
    {
        $dept_ar[] = $dept_obj->dept;
    }

    return $dept_ar;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: none
// returns: an array of department abbreviations (e.g. ARTH, CSCI, HIST, etc.)
// NOTE: this uses Williams-specific tables!
function getAllGradYears()
{
    global $CFG;

    $sql = "
SELECT DISTINCT
  wu.wms_class AS gradyear
FROM
  wms_card_ps_users AS wu
WHERE
  wu.inst_role = 1
  AND wms_affiliation = 'UGRD'
ORDER BY
  gradyear";

    $gradyear_objs = sus_get_records_sql($sql);

    // convert array of objects to array of strings
    $gradyear_ar = array();
    foreach ($gradyear_objs as $gradyear_obj)
    {
        $gradyear_ar[] = $gradyear_obj->gradyear;
    }

    return $gradyear_ar;
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id
// returns: a single object, which is a blank access item for a the given sheet
function newAccess($sheet_id)
{
    global $USER;

    $newaccess = array(
        'created_at' => time(),
        'updated_at' => time(),
        'last_user_id' => $USER->id,
        'sheet_id' => $sheet_id,
        'type' => '',
        'constraint_id' => '',
        'constraint_data' => '',
        'broadness' => -1
    );

    return (object)$newaccess;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an access object (as returned from newAccess)
// does:  adds that access to the DB
// returns: the ID of the newly added access, or 0 on failure
function addAccessPermission($access)
{
    switch ($access->type)
    {
        case 'adminbyuser':
            $access->broadness = 1;
            break;
        case 'byuser':
            $access->broadness = 10;
            break;
        case 'bycourse':
            $access->broadness = 20;
            break;
        case 'byinstr':
            $access->broadness = 30;
            break;
        case 'bydept':
            $access->broadness = 40;
            break;
        case 'bygradyear':
            $access->broadness = 50;
            break;
        case 'byrole':
            $access->broadness = 60;
            break;
        case 'byhasaccount':
            $access->broadness = 70;
            break;
//        case '':
//            break;
        default:
            error("unknown access type: ".$access->type);
            break;
    }

    return insert_record('sus_access',$access,true);
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: an access ID or else an access object
// does:  delete the access record, by ID if that was provided, or the record that matches the given object
// returns: true on success, or false on failure
function removeAccessPermission($access_id_or_object)
{
    global $CFG;

    //debugging("access_id_or_object is $access_id_or_object\n");

    if (is_object($access_id_or_object))
    {
        $sql = "
DELETE FROM {$CFG->prefix}sus_access 
WHERE 
  sheet_id={$access_id_or_object->sheet_id}
  AND type='{$access_id_or_object->type}'
";
        if (($access_id_or_object->type=='bycourse')
            || ($access_id_or_object->type=='byinstr'))
        {
            $sql .= "  AND constraint_id = {$access_id_or_object->constraint_id}";
        } else if (($access_id_or_object->type=='bydept')
                   || ($access_id_or_object->type=='byuser')
                   || ($access_id_or_object->type=='adminbyuser')
                   || ($access_id_or_object->type=='bygradyear'))
        {
            $sql .= "  AND constraint_data = '{$access_id_or_object->constraint_data}'";
        } else
        {
            $sql .= "  AND 1=0"; // if type is unknown, make sure we don't delete anything
        }

        //debugging("object based sql is $sql\n");

        return execute_sql($sql, false); // second param turns off direct-to-screen feedback
    } else if (is_numeric($access_id_or_object)) {
        $sql = "DELETE FROM {$CFG->prefix}sus_access WHERE id = $access_id_or_object";

        //debugging("number based sql is $sql\n");

        return execute_sql($sql, false); // second param turns off direct-to-screen feedback
    } else
    {
        //debugging("neither object nor int, apparently\n");

        return false;
    }
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a sheet id
// returns: a complex data structure, holding both organized arrays of
//  access objects and organized arrays object ids. The top level keys
//  are the access types (e.g. 'by course','byinstr', etc.), or the
//  string 'data_or_ids_of_' followed by the types
//  (e.g. 'data_or_ids_of_bycourse'), or the string 'keyed_' followed by
//  the types (e.g. 'keyed_byuser'). The second levels are lists approp
//  access objects for the type keys, lists of the constraint data or
//  constraint id objects for the data_or_ids_of_ keys, and assoc arrays
//  of access object ids keyed by constraint data or constraint id for the
//  keyed_ keys.
function getAccessPermissions($sheet_id,$access_type='')
{
    global $CFG,$ACCESS_FIELDS;

    $sql = "
SELECT
$ACCESS_FIELDS
FROM {$CFG->prefix}sus_access AS ac
WHERE
  ac.sheet_id = $sheet_id
".($access_type?"  AND ac.type='$access_type'":'')."
ORDER BY
  type
";

    //debugging("sql is\n$sql\n\n");

    // get the results
    $access_objs = sus_get_records_sql($sql);

    // convert to approp hierarchical array structure
    $ret = array();
    foreach ($access_objs as $access)
    {
      if (! isset($ret[$access->a_type]))
      {
        $ret[$access->a_type] = array();
        $ret['data_or_ids_of_'.$access->a_type] = array();
        $ret['keyed_'.$access->a_type] = array();
      }
      $ret[$access->a_type][] = $access;
      if ($access->a_constraint_id)
      {
        $ret['data_or_ids_of_'.$access->a_type][] = $access->a_constraint_id;
        $ret['keyed_'.$access->a_type][$access->a_constraint_id] = $access->a_id;
      } else if ($access->a_constraint_data)
      {
        $ret['data_or_ids_of_'.$access->a_type][] = $access->a_constraint_data;
        $ret['keyed_'.$access->a_type][$access->a_constraint_data] = $access->a_id;
      }
    }

    return $ret;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a user id, a sheet id, and an access id
// returns: true if the given access id allows the given user to signup on the given sheet, false otherwise
function userHasSignupAccess($user_id,$sheet_id,$access_id)
{
    debug(4,"userHasSignupAccess($user_id,$sheet_id,$access_id)");
    $res = getSignupAccessibleSheets(false,$user_id,$sheet_id,$access_id);
    debug_r(4,$res);
    return ($res && (count($res)>0));
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a user id, a sheet id
// returns: true if the given user owns or has been granted admin access to the given sheet, false otherwise
function userHasAdminAccess($user_id,$sheet_id)
{
    global $CFG;

    $sql = "
SELECT
  s.id
FROM
  {$CFG->prefix}sus_sheets AS s
WHERE
  s.owner_user_id = $user_id
  AND s.id = $sheet_id
UNION
SELECT
  s.id
FROM
  {$CFG->prefix}sus_sheets AS s
  JOIN {$CFG->prefix}sus_access AS a ON a.sheet_id = s.id AND a.type='adminbyuser'
  JOIN {$CFG->prefix}user AS u ON u.username=a.constraint_data AND a.type='adminbyuser'
WHERE
  s.id = $sheet_id";

  $res = sus_get_records_sql($sql);

  return ($res && (count($res) > 0));

}

////////////////////////////////////////////////////////////////////////////////////////
// takes:
//    an email address TO (non-empty)
//    a subject (non-empty)
//    a message body
// does: emails that message to that address, coming from glow default mailer
// returns: true on success, false otherwise
function simpleEmail($to,$subject,$body)
{
    return sendEmail(array($to),array(),array(),'','',$subject,$body);
}


////////////////////////////////////////////////////////////////////////////////////////

// takes:
//    an array of addresses to TO (must have at least one element)
//    an array of addresses to CC (may be empty)
//    an array of addresses to BCC (may be empty)
//    a FROM address (glow_mailer_no_reply@williams.edu is used if this is blank)
//    a REPLY-TO address (FROM is used if this is blank)
//    a string that's the message SUBJECT (must be non-empty)
//    a string that's the message BODY (may be empty)
// does: send an email to the given recipients
//     NOTE: this does NOT check any of the various email preferences for
//     indiv users - that is assumed to be handled during the recipient list
//     construction before this is called
// returns: true if successful, false otherwise
function sendEmail($to_ar,$cc_ar,$bcc_ar,$from,$replyto,$subject,$body)
{
    global $CFG, $FULLME, $USER;

    if (!empty($CFG->noemailever)) {
        // hidden setting for development sites, set in config.php if needed
        return true;
    }

    ///////////////////////////////////
    // validation and setting defaults
    if (count($to_ar) < 1) { return false; }
    if (! $from) { $from = "glow_mailer_no_reply@williams.edu"; }
    if (! $replyto) { $replyto = $from; }
    if (! $subject) { return false; }

    /////////////////////////////////
    // processing and setting headers

    $mail =& get_mailer();
    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    // make up an email address for handling bounces
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V',$USER->id)).substr(md5($USER->email),0,16);
        $mail->Sender = generate_email_processing_address(0,$modargs);
    } else {
        $supportuser = generate_email_supportuser();
        $mail->Sender = $supportuser->email;
    }

    foreach ($to_ar as $to_addr)
    {
        $mail->AddAddress($to_addr);
    }
    foreach ($cc_ar as $cc_addr)
    {
        $mail->AddCC($cc_addr); 
    }
    foreach ($bcc_ar as $bcc_addr)
    {
        $mail->AddBCC($bcc_addr); 
    }
    $mail->From     = stripslashes($from);
    $mail->FromName = 'Glow SUS';
    $mail->AddReplyTo($replyto);

    //////////////////////////////////
    // processing and setting message    

    // clean up escaped quotes on subject and body of email
    $subject = preg_replace("/\\\\'/","'",$subject);
    $subject = preg_replace('/\\\\"/','"',$subject);
    $body = preg_replace("/\\\\'/","'",$body);
    $body = preg_replace('/\\\\"/','"',$body);

    $mail->Subject  = substr($subject, 0, 900);
    $mail->IsHTML(false);
    $mail->WordWrap = 79;
    $mail->Body =  "\n$body\n";

    ///////////////////
    // send the email 

    if ($mail->Send()) {
        $mail->IsSMTP();                               // use SMTP directly
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
        mtrace('ERROR: '. $mail->ErrorInfo);
        add_to_log(SITEID, 'library', 'mailer', $FULLME, 'ERROR: '. $mail->ErrorInfo);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }

}

////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
// display and other utility functions
////////////////////////////////////////////////////////////////////////////////////////

# VARIOUS unix timestamp manip functions. All
# takes: a unix timestamp (as created by mktime or as used by the moodle DB system for dates et al)
# NOTE: all the timeAsDate functions use 10:10:10 as the time instead of 0:0:0 because the latter causes some date arithmatic problems
# NOTE: all the timeAsDate functions return a unix timestamp (from mktime)

# returns: the first day of the month of that date
function firstOfMonth_timeAsDate($t)
{
    return mktime(10,10,10,date('n',$t),1,date('Y',$t));
}

# returns: the last day of the month of that date
function lastOfMonth_timeAsDate($t)
{
    return  mktime(10,10,10,date('n',$t),date('t',$t),date('Y',$t));
}

# returns: the date after the given date
function dayAfter_timeAsDate($t)
{
    // NOTE: can't just add 86400 here - gives a minor offset that will cause a problem over time.
    // Instead, add extra (+1000), then truncate to the day.
    $next = $t + 87400;
    return mktime(10,10,10,date('n',$next),date('d',$next),date('Y',$next));
}

# returns: the date before the given date
function dayBefore_timeAsDate($t)
{
    // NOTE: can't just subtract 86400 here - gives a minor offset that will cause a problem over time.
    // Instead, shave off less (-1000), then truncate to the day.
    $prev = $t - 85400;
    return mktime(10,10,10,date('n',$prev),date('d',$prev),date('Y',$prev));
}

# takes: an optional time (use '' to skip), an option delimeter
# returns: a string for the date in YYYY$delimiterMM$delimiterDD format
# if no param is given, returns based on current date
function ymd($t='',$delim='')
{
    $format = "Y{$delim}m{$delim}d";
    if (! $t)
    {
        return date($format);
    }
    return date($format,$t);
}

# takes: an optional time (use '' to skip), an option delimeter
# returns: a string for the date in YYYY$delimiterMM$delimiterDD HH:MI AP format
# if no param is given, returns based on current date
function ymd_hm_a($t='',$delim='')
{
    $format = "Y{$delim}m{$delim}d h:i A";
    if (! $t)
    {
        return date($format);
    }
    return date($format,$t);
}

# takes: an optional time (use '' to skip)
# returns: a string for the date in H:MI AP format
function hmi_a($t='')
{
    $format = "g:i A";
    if (! $t)
    {
        return date($format);
    }
    return date($format,$t);
}

# returns: the number of the week day of the date (1==sunday through 7=saturday)
function getDow($t)
{
   return (date('N',$t) % 7) + 1; // shift sunday to first day of week
}

# returns: true if the given date is the first of a month
function isFirstOfMonth($t)
{
    return (date('d',$t) == 1);
}

# return: true if the given date is a saturday or sunday
function isWeekend($t)
{
    $t_dow = getDow($t);
    return ($t_dow==1 || $t_dow==7);
}

# returns: the number of week rows in the month containing the date
function getNumWeekRowsInMonth($t)
{
     $first_day = getDow(firstOfMonth_timeAsDate($t));

    // num rows in a month = (first dow (0 based) + last day in month + 7) div 7
    $num_week_rows = intval(($first_day - 1 + date('t',$t) + 7) / 7);
    if (($first_day - 1 + date('t',$t)) % 7 == 0) $num_week_rows--;

    return $num_week_rows;
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: a preselect value, a max value, and an optional increment value, and an optional padleft value
// returns: an option set suitable for a select control. Range is 1 to $max, the $presel option is selected. If increment is provided, the step in the range is by that increment (defaults to 1). If padleft is provided, the displayed values are padded left with 0's until at least that many spaces are filled.
function getOptions ($presel,$min,$max,$incr=1,$padlefttil=0)
{
    if ($incr <= 0)
    {
        $incr = 1;
    }
    $opts = '';
    for ($i = $min; $i <= $max; $i+=$incr)
    {
        $opts .= "  <option value=\"$i\"".($presel==$i?' selected="selected"':'').">".padLeft($i,$padlefttil)."</option>\n";
    }
    return $opts;
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: the string to pad, the total number of digits there should be
// returns: the string left padded with 0's so the total length is $places
function padLeft($n,$places)
{
    $ret = $n;
    for ($p=$places-strlen($n); $p > 0; $p--)
    {
        $ret="0$ret";
    }
    return $ret;
}


////////////////////////////////////////////////////////////////////////////////////////

// takes: a value which will be displayed in HTML, a flag indication whether quotes should be escaped or left alone
// does: echoes a cleaned version of that value to the screen
// NOTE: clean means safe to display or put as an attribute value
function cleanecho($txt,$flag_leave_quotes=false)
{
    $str = preg_replace('/\"/','\&#34;',clean_param($txt,PARAM_CLEAN));
    if ($flag_leave_quotes)
    {
        $str = preg_replace('|\\\\\'|',"'",$str);
    }
    echo $str;
    //echo preg_replace('/\"/','\&#34;',clean_param($txt,PARAM_CLEAN));
}

////////////////////////////////////////////////////////////////////////////////////////

// takes: a form input value (generally from a checkbox)
// returns: 1 if the input is true, 0 otherwise
function fromCheckbox ($val)
{
    if ($val) return 1;
    return 0;
}


////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

// takes: a debuyg level, an object or array
// does: if DEBUG is greater than the level, display it to the screen, wrapped in pre tags
function debug_r($level,$obj)
{
    global $DEBUG;

    if ($DEBUG >= $level)
    {
        for ($i=0; $i < $level; $i++) {echo '&nbsp; ';}
        echo "$level:<pre>";
        print_r($obj);
        echo '</pre>';
    }
}

// takes: a debuyg level, a string/value
// does: if DEBUG is greater than the level, display it to the screen, wrapped in pre tags
function debug($level,$msg)
{
    global $DEBUG;

    if ($DEBUG >= $level)
    {
        for ($i=0; $i < $level; $i++) {echo '&nbsp; ';}
        echo "$level: $msg<br/>\n";
    }
}

// takes: an object or array
// does: dumps it to the error log
function log_debug_r($level,$obj)
{
    global $DEBUG;

    if ($DEBUG >= $level)
    {
        $to_log = "$level:\n";
        $indent = '';
        for ($i=0; $i < $level; $i++) {$indent .= '  ';}
        $obj_ar = (array)$obj;
        foreach ($obj_ar as $key => $val)
        {
          if (is_scalar($val))
          {
              $to_log .= "$indent$key = $val\n";
          } else
          {
              $to_log .= "$indent$key = ".gettype($val)."\n";
          }
        }
        debugging($to_log);
    }
}

// takes: a debuyg level, a string/value
// does: if DEBUG is greater than the level, display it to the screen, wrapped in pre tags
function log_debug($level,$msg)
{
    global $DEBUG;

    if ($DEBUG >= $level)
    {
        $indent = '';
        for ($i=0; $i < $level; $i++) {$indent .= '  ';}
        debugging("$indent$level: $msg\n");
    }
}


////////////////////////////////////////////////////////////////////////////////////////
/// REPLACED FUNCTIONS                                                              ///
////////////////////////////////////////////////////////////////////////////////////////
/// these are replacements for functions that would normall be in /lib/dmlib.ph, except
/// those functions DON'T WORK! Or at least, not the way they should.
////////////////////////////////////////////////////////////////////////////////////////

/**
 * This is a utility function that converts a record set to an array. The original version converted it
 * to an associative array, keyed by the first column of the query. However, what I really need is an 
 * actual array, with ordinal keys, and one element per record returned. Stupid moodle.
 *
 * NOTE: this relies on a mysql back end, I've taken out all the oracle related hacks
 *
 * @param object an ADODB RecordSet object.
 * @return mixed mixed an array of objects, or false if an error occured or the RecordSet was empty.
 */
function sus_recordset_to_array($rs) {
    global $CFG;

    $debugging = debugging('', DEBUG_DEVELOPER);

    if ($rs && !rs_EOF($rs)) {
        $objects = array();
        if ( $records = $rs->GetRows()) {
            foreach ($records as $record) {
                $objects[] = (object)$record;
            }
            return $objects;
        } else {
          return false;
        }
    } else {
          return false;
    }
}


////////////////////////////////////////////////////////////////////////////////////////

/**
 * This replaces the corresponding function in dmlib.php. The change
 * is it uses the sus_recordset_to_array utility function, which result in a true array
 * of results rather than assoc array.
 *
 * Get a number of records as an array of objects.
 *
 * Return an array of record objects
 *
 * @param string $sql the SQL select query to execute. The first column of this SELECT statement
 *   must be a unique value (usually the 'id' field), as it will be used as the key of the
 *   returned array.
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found or an error occured.
 */
function sus_get_records_sql($sql, $limitfrom='', $limitnum='') {
    $rs = get_recordset_sql($sql, $limitfrom, $limitnum);
    return sus_recordset_to_array($rs);
}

function sus_grammatical_max_signups ($num){
   if (! $num || $num < 0){
	return 'an unlimited number of signups';
   }
   else if ($num == 1){
   	return "1 signup";
   } 
   else {
   	return "$num signups";
   }
}
?>