/* sus == sign-up sheets */

LABEL {
  font-weight: bold; 
   margin-right:5px;
}

.sus_right {
  float: right;
}

.sus_left {
  float: left;
}

.sus_dangerous,
.sus_dangerous a,
.sus_dangerous a:visited
{
  font-weight: bold !important;
  color: #dd0000 !important;
}

.sus_small {
  font-size: 12px;
}

.sus_very_small {
  font-size: 8pt;
}

.spacer_bg {
  height: 15px;
  background: #37585E;
  margin-left: -10px;
  margin-right: -10px;
}

/*------------------------------*/
/* block styles                 */
/*------------------------------*/

.sus_block_body {
}

.sus_block_body h4 {
  margin: 0; 
  padding: 0;
}
.sus_block_body h5 {
  margin: 5px 0 3px 0; 
  padding: 0;
  border-bottom:1px solid #ccc;
}

.sus_tool_links {
  text-align: center;
  font-weight: bold;
  margin-top:5px;
}
.sus_block_body .sus_tool_links {
 background:#EEE;
 border-bottom:1px solid #ccc;
 margin:-6px;
 margin-bottom:7px;
 padding:3px;
 padding-top:4px;
}
.block_signup_sheets .opening_day {
  display: block;
  font-size: 12px;
}
.block_signup_sheets ul.my_signups_list {
  margin-top: 1px;
  margin-bottom: 4px;
}

.block_signup_sheets ul.my_signups_list,
.block_signup_sheets ul.my_signups_list ul
{
  list-style: none;
  padding-left: 6px;
}
.block_signup_sheets ul.my_signups_list li li li{
 list-style-type:disc;
 list-style-position:inside;
}
.block_signup_sheets ul.my_signups_list li
{
  font-size: 8pt;
/*  letter-spacing: -.1em;*/
  margin-bottom:3px;
}

.block_signup_sheets .remove_signup,
.block_signup_sheets .opening_capacity,
.block_signup_sheets .sheet_details,
.block_signup_sheets .sus_username
{
  display: none;
}

.block_signup_sheets .sheet_name {
  border: none;
}

.block_signup_sheets .signup_detail_info {
  border: 1px solid #222;
  background: #fff;
  padding: 3px;
  padding-left: 6px;
  width: 200px;
  position: absolute;
  left: -999px;
  top: auto;
  letter-spacing: 0;
  font-size: 9px;
}
.block_signup_sheets .signup_detail_info div {
  font-size: 10px;
}

/*------------------------------*/
/* block config styles          */
/*------------------------------*/

.blockconfiginstance {
  background: #e5e5d9 !important;
}

#sus_config {
  width: 800px;
  margin: 0 auto;
}

#sus_config .config_label {
  width: 300px;
  float: left;
  font-size: 14px;
  text-align: right;
  padding: 6px;
}

#sus_config .config_value {
  width: 450px;
  float: left;
  font-size: 14px;
  padding: 6px;
}

#sus_config .config_save {
  text-align: center;
  margin: 10px;
  margin-top: 30px;
}

#sus_config .config_save input {
  margin: 0;
  padding: 4px;
  font-size: 18px;
}

/*------------------------------*/
/* tool styles                  */
/*------------------------------*/

.sus_content {
  text-align: center;
}

.sus_nav {
  text-align: center;
  width: 690px;
  margin: 3px auto;
}

.sus_nav ul {
  padding: 0px;
}

.sus_nav ul li {
  float: left;
  list-style: none;
  margin: 10px;
  font-size: 16px;
  width: 150px;
  height: 22px;
}

.sus_nav ul li .curaction {
  font-weight: bold;
}

#sus_user_notify {
  width: 300px;
  padding: 10px;
  position: fixed;
  left: 300px;
  top: 150px;
  border: 1px solid #D37C2C;
  background-color: white;
  display: none;
  font-size: 12pt;
  font-weight:bold;
  color:#D37C2C;
}

#sus_custom_alert {
/*  height: 250px;*/
  width: 320px;
  position: fixed;
  left: -999px;
  top: 150px;
  border: 1px solid #D37C2C;
  background: #fff;
  display: none;
  font-size: 16px;
  color: #000;
}
#sus_custom_alert h1 {
  font-size: 16px;
  text-align: center;
  background: #D37C2C;
  margin-bottom: 8px;
  padding:3px;
  color:white;
}
#sus_custom_alert .alert_text {
  padding: 6px;
  font-size:11pt;
  color: #000;
}
#sus_custom_alert #custom_alert_close {
 margin:7px;
  font-size:10pt !important;
  font-weight: bold;
  padding:3px 6px;
  color:white;
  background-color:#9E8A44;
  border: 1px solid #D37C2C;
  cursor:pointer;
  text-align: center;

}


.sus_timestamp_info {
  font-size: 11px;
  margin-bottom: 8px;
  color: #37585E;
  font-style:italic;
}

.sus_action_button {
  margin:4px;
  font-size:11px !important;
  font-weight: bold;
  padding:2px 4px;
  color:white;
  background-color:#D37C2C;
  border: 1px solid white;
  cursor:pointer;
  text-align: center;
}

.sus_action_button_med {
  margin:7px;
  font-size:10pt !important;
  font-weight: bold;
  padding:3px 6px;
  color:white;
  background-color:#D37C2C;
  border: 1px solid white;
  cursor:pointer;
  width:130px;
  text-align: center;
}

.sus_action_button_large {
  margin:10px;
  font-size:10pt !important;
  font-weight: bold;
  padding:4px 8px;
  color:white;
  background-color:#D37C2C;
  border: 1px solid white;
  cursor:pointer;
  width:160px;
  text-align: center;
}


.sus_delete_group {
  background-color:white;
  border-color:#D37C2C;
}

.sus_action_button_small {
  margin: 16px 24px;
  font-size: 12px !important;
  width: 64px;
  height: 24px;
  padding-bottom: 10px;
}

.sus_action_box {
  font-size: 12px !important;
  font-weight: bold;
  border: 1px solid #555;  
  background: #eee;
  width: 16px;
  height: 16px;
  text-align: center;
  padding-left: 3px;
  padding-right: 2px;
}

.sus_action_box a {
  text-decoration: none;
}

.sus_choose_date {
  width: 110px;
  padding:2px;
  text-align: center;
  background-image: url(/blocks/signup_sheets/image/shaded_square.jpg);
  margin: 5px 0 15px 0;
  border: 1px solid grey;
  font-weight: bold;
  font-size:10pt !important;
}

.optional_input {
  color: #555;
  font-style: italic;
}

/* these two replace standard HX tags, which tabber strips out */
.hh1 {
  font-weight: bold;
  font-size: 16px;
  margin: 6px 0;
}
.hh2 {
  font-weight: bold;
  font-size: 14px;
  margin: 3px;
  margin-left: 6px;
  margin-top: 8px;
}
.hh3 {
  font-weight: bold;
  font-size: 11px;
  margin: 2px;
  margin-left: 8px;
  margin-top: 8px;
}
.hh4 {
  text-decoration: underline;
  font-size: 11px;
  margin: 2px;
  margin-left: 10px;
  margin-top: 8px;
}

/*------------------------------*/
/* help docs                    */
/*------------------------------*/

.sus_help_content {
  width: 700px;
  margin:0 auto;
  text-align:left;
}


.sus_help_content h1,
   .sus_help_content h2,
   .sus_help_content h3,
   .sus_help_content h4,
   .sus_help_content h5 { font-weight: bold; padding-left: 0; padding-right: 0; padding-top: 3px; margin-left: 0; margin-right: 0;}

/*  border-bottom: 2px solid #000; */


.sus_help_content h1 { font-size: 24px; margin-top: 14px; border-top: 3px solid #000;}
.sus_help_content h2 { font-size: 20px; margin-top: 12px; border-top: 2px solid #000;}
.sus_help_content h3 { font-size: 16px; margin-top: 10px; border-top: 2px solid #000;}
.sus_help_content h4 { font-size: 14px; margin-top: 10px; border-top: 2px solid #000;}
.sus_help_content h5 { font-size: 12px; margin-top: 10px; border-top: 2px solid #000;}

.sus_help_content img {
  border: 1px solid #3E565A;
  clear: both;
  margin: 10px;
}

#help_nav {
  padding: 10px;
  border-top: 3px solid #000;
  padding-left: 40px;
}

#help_nav li {
  list-style-type: none;
  margin-left: -20px;
}


/*------------------------------*/
/* manage sheets main           */
/*------------------------------*/

.sus_subcontent {
  width: 700px;
  margin:0 auto;
  text-align:left;
}		

ul.sus_sheetgroup_list {
  margin: 0; 
  padding:0;
  margin-top:15px;
  list-style: none;
}

.sus_sheetgroup_item {
  margin-bottom: 10px;
  border:1px solid #3E565A;
  background:white;
  padding-bottom:10px;
}
.sus_new_sheetgroup {
  padding:10px;
}
.sus_admin_sheets {
  padding:10px;
}

.sus_sheetgroup_list h3 {
  font-size:1.1em;
  background-color:#3E565A;
  padding:10px;		
}
.sus_sheetgroup_list h3 a{
  color:white;
  border-bottom:1px solid white;
}
.sus_sheetgroup_list h3 a:hover{
  color:white;
  border-bottom:none;
  text-decoration:none;
}
.sus_sheetgroup_list h3 a.nukeit_link { border:0; }

ul.sus_sheet_list {
  font-size: 12px;
  list-style: none;
  margin:0; padding:0;
}

.sus_sheet_item {
  margin-top: 5px;
  margin-left: 10px;
}

.sus_new_sheet {
  font-weight: bold;
}

.sus_new_sheetgroup {
  font-weight: bold;
}

.sus_temp_data {
  color: #666;
}

.sus_formtext {
  margin-top:10px;
}

#input_max_pending {
  width:32px;
}

.sus_none {
  font-style: italic;
  margin-bottom:6px;
}

#action_button_box {
  text-align:center;
  margin:0;
  clear:both;
}

/*------------------------------*/
/* edit sheet group             */
/*------------------------------*/

.sus_sheetgroup_data {
  text-align: left;
  width: 610px;
  margin: 10px auto;
}

.sus_sheetgroup_data div {
  margin: 6px;
} 


.sus_sheetgroup_data ul.sus_sheet_list {
  list-style: none;
  margin: 2px;
}

.sus_sheetgroup_data .sus_sheet_item {
  margin-top: 5px;
}

#input_sheetgroup_name {
  width: 600px;
  height: 36px;
}

#text_sheetgroup_description {
  font-size: 11pt;
  width: 600px;
  height: 120px;
}

.sus_new_sheet_item {
  margin-top: 10px;
  border:1px solid #3E565A;
  background:white;
  padding:10px;
}

/*------------------------------*/
/* edit sheet                   */
/*------------------------------*/

.sus_sheet_data {
  text-align: left;
  width: 810px;
  margin: 0 auto;
}


.sus_sheet_data_left {
  width: 325px;
  width:340px;
  float: left;
}

.sus_sheet_data_right {
  width: 450px;
  float: right;
}

/* .sus_sheet_data div {
  margin: 6px;
}
*/

/********* sheet info form (left) *********/

.sus_indent{
  margin-left:10px;
  margin-top:6px;
}
.sus_spacer { 
  height:10px;
}

#sheet_group_id {
}

/*#select_sheet_group_id {
  font-size: 16px;
}*/

#sheet_name { 
  font-weight:bold;
  font-size:1.2em;
}
#input_sheet_name{
  width:180px;
  padding:2px;
}

#text_sheet_description {
/*  font-size: 16px;*/
  width: 300px;
  height: 120px;
  margin-bottom:10px;
}

#sheet_active_time_range {
  margin-bottom: 12px;
}

.ui-datepicker-calendar .ui-datepicker-unselectable {
  text-align: right;
  padding-right: 5px;
}

.ui-datepicker-div div.ui-datepicker-buttonpane {
  
  margin-top: 1px;
  padding-top: 1px;
  border: 3px solid red;
}

#sus_sheet_flags .hh1 {
  font-weight: bold;
  font-size: 16px;
  margin: 14px 0 2px 0;
}
#sus_sheet_flags .hh2 {
  font-weight: bold;
  font-size: 14px;
  margin: 2px;
  margin-left: 6px;
  margin-bottom: 4px;
}


#sus_owner_alerts {
  width: 45%;
  float: left;
  font-size: 12px;
  margin-bottom: 12px;
}

#sus_admin_alerts {
  width: 45%;
  float: left;
  font-size: 12px;
  margin-bottom: 12px;
}

.alert_option {
  font-size: 14px;
  vertical-align: middle;
  margin: 2px;
}

/********* sheet access **********/

#access_to_signup {
  font-size:11px;
}

#access_to_signup .sus_very_small,
#access_to_admin .sus_very_small{
  margin-left:18px;
}

.sus_sheet_data #access_to_signup input {
  font-size: 11px;
}

.cb_list {
  border: 1px solid #888;
  overflow: auto;
}

#signup_privacy_settings {
  width: 280px;
  padding: 3px;
  margin-left: 10px;

}

#access_by_course_list,
#access_by_instr_list,
#access_by_dept_list,
#access_by_gradyear_list,
#access_by_role_list
{
  width: 280px;
  height: 80px;
  padding: 3px;
  margin-left: 10px;
}

#access_by_dept_list,
#access_by_gradyear_list
{
  width: 110px;
}

#dept_and_gy_side_by_side {
  height: 136px;
}

#access_by_dept,
#access_by_gradyear
{
  float: left;
  width: 130px;
  height: 80px;
  padding: 3px;
  margin-left: 10px;
}

#access_by_role {
  clear: both;
}

#access_by_role_list {
  height: 70px;
  clear: both;
}

#access_by_user_list,
#admin_by_user_list {
  border: 1px solid #888;
  width: 280px;
  height: 80px;
  margin-left: 10px;
}

#access_by_any_box {
  border: 1px solid #888;
  padding: 3px;
  width: 280px;
  height: 24px;
  margin-left: 10px;
}

/********* tabbed panel stuff is in a different file - tab.css *********/

/********* calendar formating **********/

.full_calendar {
  float:left;
  width:430px;
}

.month_title {
  background:#595938;
  color:#D5CD9C;
  border: 1px solid black;
  float: left;
  clear: both;
  width: 42px;
  font-size: 20px;
  font-weight: bold;
  text-align: center;
}
.month_title p { margin-top:20px;}
.month_grid {
  float: left;
}

.week_row {
  border: 0;
  margin: 0;
  clear: both;
}

.day_cell {
  float: left;
  border: 1px solid black;
  background: #e2e2a7;
  margin: 0;
  width: 44px;
  height: 36px;
  padding: 4px;
  font-size: 15px;
  font-weight:bold;
  line-height: 18px;
}

.day_cell a:hover {
  text-decoration: none;
}

.day_header {
  font-size: 12px;
  font-weight: bold;
  background: #37585e;
  color:white;
  height: 18px;
  text-align: center;
  padding: 4px;
/*  padding-top: 2px;*/
}

/* weekend */
.cal_we { 
  background: #bba350;
}

/* inactive weekday */
.cal_inactive_wd {
  color: #999;
  background: #ced1d1;
}

/* inactive weekend */
.cal_inactive_we {
  color: #666;
  background: #a3a5a5;
}

/* in the active time range, but in the past */
.cal_past_wd {
  background: #d5cd9c;
}
.cal_past_we {
  background: #9e8a44;
}

.cal_today {

  border:3px solid #cc2e09;
/*  width: 40px;
  border: 3px solid #22d;
  height: 32px; */
  color: #cc2e09;
  padding: 2px;
}

/*
.day_has_openings {
}
*/

.cal_gone {
  background: #E5E5D9;
  border: 1px solid #999;
  /*border: none;
  width: 38px;
  height: 38px;*/
}

/********* opening info formating **********/

.day_openings_minitimes {
  height: 25px; /* change to 27 if showing workday bars */
  width: 18px;
  margin: 0px;
  /* margin-top: -18px; */
  background: #ccc;
  border: 2px solid #373;
  padding: 0;
  float: right;
}

.day_openings_minitimes .noon_split,
.day_openings_minitimes .workday_split,
.day_openings_minitimes .is_full,
.day_openings_minitimes .is_free {
  height: 1px;
  width: 18px;
  margin: 0;
  padding: 0;
}

.day_openings_minitimes .noon_split {
  background: #fff;
}

.day_openings_minitimes .workday_split {
  background: #ccc;
}

.day_openings_minitimes .is_full {
  background: #33b;
}

.day_openings_minitimes .is_free {
  background: #9a9;
}

.openings_summary_box {
  color:black;		    
  position: absolute;
  background: #fff;
  border: 1px solid black;
  left: -999px;
  width: 300px;
  font-size: 12px;
  padding-left: 6px;
}

.day_cell .openings_summary_box {
  font-weight: normal;
}

.openings_summary_box .long_sign_up,
.openings_summary_box .opening_name,
.openings_summary_box .opening_description {
  display: none;
}

.opening_list_format .short_sign_up,
#day_openings_full_details .short_sign_up {
  display: none;
}

.openings_summary_box .msg_signed_up {
  font-size: 10px;
  font-weight: bold;
}

.opening_header_info {
}

.user_is_signee {
  font-weight: bold;
}

.opening_signup_link_box {
  margin-left: 0;
  display: inline;
  font-weight: normal;
}
.opening_signup_link_box:hover {
  text-decoration: underline;
}
.opening_signup_link {
  font-size: 10px;
  display: inline;
}



.short_sign_up {
  font-size: 10px;
}

.long_sign_up {
  color: #C0652C;
}
.long_sign_up:hover,
.short_sign_up:hover {
  cursor:pointer;
  text-decoration: underline;
}

.nukeit, .addit {
  cursor:pointer;
  margin-right: 5px;
  margin-left:5px;
  border:0;
}

.openings_summary_box .nukeit, .openings_summary_box .addit {
  margin-bottom:-2px;
}
.sus_block_body .nukeit {
  display:none;
/*  margin:0;
  margin-right:2px;
  margin-bottom:-2px;*/
}
.sus_user_fullname {
   cursor:pointer;
}

.opening_day_heading {
  display: block;
  font-size: 18px;
  font-weight: bold;
  margin-top: 6px;
  margin-bottom: 6px;
}

.opening_day {
  display: none;
  font-size: 14px;
  font-weight: bold;
}

.opening_timerange {
  display: inline
  font-size: 16px;
  font-style: italic;
}

.opening_capacity {
  font-weight: bold;
}

.opening_has_space {
  color: #4b4;
}

.opening_is_full {
  color: #b44;
}

.opening_description {
  margin-top: 3px;
  margin-left: 6px;
}

ul.opening_signees_list {
  margin-top: 4px;
  margin-bottom: 6px;
  padding-left: 20px;
}

.openings_summary_box ul.opening_signees_list {
  margin-top: 0;
}

.openings_summary_box ul.opening_signees_list li {
  font-size: 10px;
}

.list_su_signup_details {
  font-size: 9px;
}
.openings_list ul.opening_signees_list li .list_su_signup_details,
.openings_summary_box ul.opening_signees_list li .list_su_signup_details {
  display: none;
  margin-left: -999px;
  position: relative;
  width: 120px;
  padding: 3px;
  padding-top: 0px;
  padding-bottom: 0px;
  border: 1px solid #444;
  background: #ddd;
}
.openings_list ul.opening_signees_list li:hover .list_su_signup_details {
  display: block;
  margin-left: 90px;
  margin-top: -15px;
  position: absolute;
}
.openings_summary_box ul.opening_signees_list li:hover .list_su_signup_details {
  display: block;
  margin-left: 60px;
  margin-top: -18px;
  position: absolute;
}

#day_openings_full_details ul li,
.opening_list_format ul.openings_list li {
  margin-bottom: 8px;
}

.opening_list_format ul,
.openings_summary_box ul.openings_list,
#day_openings_full_details ul.openings_list,
#day_openings_full_details ul.openings_list {
  padding-left: 1px;
  list-style: none;
}



#day_openings_full_details .opening_signup_link_box {
  display: inline;
  font-size: 10px;
}

.opening_list_format ul.openings_list .opening_signup_link_box {
  display: inline;
  font-size: 10px;
}

#day_openings_full_details
{
  float: right;
  width: 300px;
  margin-left: 12px;
  padding: 6px;
}

#signup_help
{
  float: right;
  width: 300px;
  margin-left: 12px;
  padding: 6px;
}

.opening_list_format {
}

/*------------------------------*/
/* create & edit openings       */
/*------------------------------*/

#blocks-signup_sheets-sheet_create_openings_form #header, 
#blocks-signup_sheets-sheet_create_openings_form .navbar, 
#blocks-signup_sheets-sheet_edit_opening_form #header, 
#blocks-signup_sheets-sheet_edit_opening_form .navbar,
#blocks-signup_sheets-sheet_create_openings_process #header, 
#blocks-signup_sheets-sheet_create_openings_process .navbar
{
  display: none;
}

#opening_create_edit .optional_opening_fields {
  display: none;
}
#blocks-signup_sheets-sheet_edit_opening_form .for_opening_printing {
  display: none;
}

#opening_create_edit .optional_opening_fields_show,
#opening_create_edit .optional_opening_fields_hide {
  text-decoration: underline;
  width: 120px;
  text-align: center;
  margin: 0px auto;
  margin-bottom: 6px;
}

.timechooser {
  width: 50px;
}

#opening_create_edit LABEL {
  width:200px;
  display:block;
  float:left;
  text-align:right;
  margin-lefT:5px;
  clear:both;
}

#blocks-signup_sheets-sheet_edit_opening_form #opening_create_edit LABEL {
  width:130px;
}
#blocks-signup_sheets-sheet_edit_opening_form #opening_send_email LABEL {
  width:120px;
}

#blocks-signup_sheets-sheet_edit_opening_form #opening_create_edit .opening_signees_list LABEL {
  width:40px;
}

#opening_create_edit #opening_name,
#opening_create_edit #opening_location,
#opening_create_edit .text_entry {
  width:220px;
  margin-bottom:10px; 
}
#opening_send_email .text_entry {
  width:440px;
}

#opening_create_edit SELECT {
  margin-bottom:10px; 
}
#opening_create_edit .label_more_info{
  font-weight:normal;
  line-height:.9;
}

#blocks-signup_sheets-sheet_edit_opening_form #opening_create_edit TEXTAREA.su_admin_comment {
  width:220px;
  height:30px;
  margin-bottom:10px;
  font-size: 10px;
}


#opening_create_edit div.openings_by_time_range,
#opening_create_edit div.openings_by_duration
{
  margin-top: 0;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
}

#opening_create_edit .openings_by_duration {
  display: none;
}

#opening_create_edit #opening_spec_toggler {
  text-decoration: underline;
}


#blocks-signup_sheets-sheet_edit_opening_form #opening_create_edit .sus_choose_date {
  width: 110px;
  padding:2px;
  text-align: center;
  background-image: url(/blocks/signup_sheets/image/shaded_square.jpg);
  margin-top: 0;
  margin-bottom: 10px;
  border: 1px solid grey;
  font-weight: bold;
  font-size:11px !important;
}

#blocks-signup_sheets-sheet_create_openings_form .sus_opening_print_button_form {
  float: right;
}

#blocks-signup_sheets-sheet_edit_opening_form .left_col {
  float: left;
  width: 400px;
}
#blocks-signup_sheets-sheet_edit_opening_form .right_col {
  float: right;
  width: 340px;
  height: 350px;
  overflow:auto;
  /*border: 1px solid red;*/
}


#blocks-signup_sheets-sheet_edit_opening_form ul.opening_signees_list {
  list-style: none;
}

#blocks-signup_sheets-sheet_edit_opening_form #no_signups_list_item {
  display: none;
}

#blocks-signup_sheets-sheet_edit_opening_form ul.opening_signees_list .signup_admin_comment {
  font-style: italic;
  padding-left: 8px;
  font-size: 10pt;
}

#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_list_item {
  font-weight: bold;
  font-size: 14px;
  color: #C0652C;
  margin-bottom: 10px;
}
#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_list_item:hover {
  text-decoration: underline;
}

#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_data_entry {
  /*border: 1px solid black;*/
  padding: 3px;
  font-size: 10px;
  display: none;
}
#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_data_entry LABEL.wide_label {
  width: 60px;
}

#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_data_entry .text_entry,
#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_data_entry textarea.su_admin_comment {
  margin-bottom: 3px;
  width: 197px;
}

#blocks-signup_sheets-sheet_edit_opening_form #listsorters {
  font-size: 11px;
  margin-left: 12px;
  font-weight: normal;
  float: right;
  margin-top: 4px;
  margin-right: 32px;
}
#blocks-signup_sheets-sheet_edit_opening_form #listsorters a {
  color: #C0652C;
}

#blocks-signup_sheets-sheet_edit_opening_form #signup_someone_data_entry #btn_new_signup {
  margin-left: 70px;
}

#blocks-signup_sheets-sheet_edit_opening_form #opening_send_email {
  padding: 10px;
}


/********* repeating **********/

#repeaterControls,
#chooseRepeatType,
#repeatWeekdayChooser,
#repeatMonthdayChooser,
#repeatUntilDate {
 width:280px;
 float:left;
}

#repeatUntilDate{
  margin-top: 12px;
}

#chooseRepeatType ul {
  list-style-type: none;
  margin:0;
  margin-left: 5px;  
  padding:0;
}

#repeatWeekdayChooser {
  display: none;
  width:280px;
  margin-left:30px;
  margin-top:5px;
}

#repeatMonthdayChooser {
  display: none;
  margin-left:25px;
  margin-top:5px;
}

#repeatUntilDate {
  display: none;
}

#repeatUntilDate #text_until_date {
  width: 120px;
  height: 16px;
  font-weight:normal;
  text-align: center;
/*  background: #eee;*/
}

.toggler_dow {
  width:40px;
  padding:2px;
  font-size:8pt !important;
  text-align: center;
}

.toggler_dom {
  width: 26px;
  height: 24px;
  text-align: center;
  margin:1px;
  font-size:8pt !important;
}

.toggle_down {
  width: 38px;
  height: 28px;
  border: 2px solid #444;
  background: #f00;
}

.toggle_up {
  width: 40px;
  height: 30px;
  border: 1px solid #999;
}

#opening_location {
  width: 150px;
}

/*------------------------------*/

/*-----------------------------------*/
/* do signup (show available sheets) */
/*-----------------------------------*/

#sus_accessible_sheets {
  width: 800px;
  text-align: left;
  margin: 0 auto;
}

#sus_accessible_sheets #course_sheets,
#sus_accessible_sheets #other_sheets {
  float: left;
  width: 355px;
  border:1px solid #37585E;
  background:white;
}
#sus_accessible_sheets #other_sheets { margin-left:16px;}

#sus_accessible_sheets #course_sheets h3,
#sus_accessible_sheets #other_sheets h3 {
  font-size:1.1em;
  padding:10px;
  background-color:#3E565A;
  color:white;
}

#sus_accessible_sheets #course_sheets ul,
#sus_accessible_sheets #other_sheets ul {
  margin-bottom: 10px;
  list-style:none;
}		  


#sus_accessible_sheets #course_sheets ul ul,
#sus_accessible_sheets #other_sheets ul ul {
  list-style:square;
}		  

/*------------------------------*/
/* do signup on sheet           */
/*------------------------------*/

#sus_signup_on_sheet_info,
#sus_signup_on_sheet_openings {
  width: 800px;
  text-align: left;
  margin: 0 auto;
}

#sus_signup_on_sheet_info #sheet_name {

}

#sus_signup_on_sheet_info #sheet_description {
color: #37585E;
}

#sus_signup_on_sheet_info #sheet_further_info {
  margin-top: 4px;
  font-size: 11px;
}

#sus_signup_on_sheet_openings {
  margin-top: 8px;
}

.signup_help_text {
/*  font-size: 20px;
  line-height: 40px;*/
}
.signup_help_text p { margin-top:10px; }

#sus_signup_on_sheet_info .highlight_good {
  color: #11cc11;
  font-weight: bold;
}

#sus_signup_on_sheet_info .highlight_bad {
  color: #cc1111;
  font-weight: bold;
}

.disabled_link {
  display: none !important;
}

/*------------------------------*/
/* my sign-ups                   */
/*------------------------------*/

#my_signups_page {
  width: 800px;
  text-align: left;
  margin: 10px auto;
}

#my_signups,
#signups_for_me {
  width: 760px;
  margin: 10px auto;
}

#my_signups ul,
#signups_for_me ul {
  list-style: none;
}

#my_signups ul ul,
#signups_for_me ul ul {
  margin-top: 6px;
  margin-bottom: 18px;  
}

#my_signups ul ul li,
#signups_for_me ul ul li {
  margin-bottom: 9px;
}

#my_signups .opening_day,
#signups_for_me .opening_day {
  display: inline;
  font-size: 20px;
}

#my_signups .my_in_past,
#signups_for_me .for_me_in_past {
  display: none;
  color: #777;
}

.my_signups_list .my_today .opening_day,
.my_signups_list .for_me_today .opening_day,
#my_signups .my_today .opening_day,
#signups_for_me .for_me_today .opening_day {
  color: #c00;
}

#my_signups .my_today,
#signups_for_me .for_me_today {
   padding-top: 4px;
   padding-left: 6px;
   padding-bottom: 1px;
   margin-bottom: 6px;
}

.my_signups_list .my_today,
.my_signups_list .for_me_today {
  background: #ffe;
  border: 1px solid #c00;
  margin-left: -3px;
  padding-left: 3px;
}


#toggle_my_past_display,
#toggle_for_me_past_display {
  width: 180px;
  padding: 3px;
  font-size: 10pt;
  background:#37585E;
  color:white;
  border:1px solid #D5CD9C;
  text-align: center;
  font-weight: bold;
}



#my_signups_page span.sheet_name, 
#my_signups_page .sus_user_fullname {
  border-bottom: 1px dashed black;
}
span.sheet_name:hover{
  cursor:pointer;
}

#my_signups_page .signup_detail_info,
#my_signups .sheet_details,
#signups_for_me .sheet_details {
  border: 1px solid #37585E;
  background: #c0d9de;
  padding: 3px;
  padding-left: 6px;
  width: 300px;
  position: absolute;
  left: -999px;
  top: auto;
}

.signup_detail_info .admin_comment {
  font-style: italic;
}