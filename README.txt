Signup Sheets is a block to allow users to create date-time based openings and specify other users that may sign up for those openings. This block is user-centric rather than course-centric.

This block works with moodle 1.9.x, and NOT with 2.x (that's a work in progress...)

INSTALLATION
------------
This block uses the standard moodle block installation process: put the files in place, enter local configuration info, visit the admin notifications page, and you're good to go.

1. download / fork the repository
2. put the files is <moodle_root>/blocks/signup_sheets (NOTE: the directory must be named "signup_sheets"!)
3. put in local configuration info in 4 functions near the end of sus_lib.php
    + sus_block_name
    + sus_moodle_name
    + sus_reminders_email_address
    + sus_default_from_email_address
4. log in to your moodle instance as admin
5. go to http://<moodleinstance>/admin/index.php (the Notifications page - usually the top link in the Site Administration block)



========================================================================================

CODE TO DO
----------
+ various pre-use variable checking to reduce log warnings/messages


PROJECT TO DO
-------------
+ developer documentation (architecture, code, styles)


TO TEST
-------
+ interaction with other blocks/modules/themes that use jquery

+ cron processes (daily reminder emails) NOTE: as of 2011/06/29 there
was some question about whether reminder emails are working, though
the alert emails are fine



WISH LIST
---------
+ ics, ical, google cal etc. integration/export

+ groups relevance
