<div class="sus_subcontent">
<h3>Sheet Groups</h3>
<p>Sheets are collected into groups. Group settings affect all sheets in the group. Sheet settings affect only that sheet.
</p>
<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

$DEBUG=1;

////////////////////////////////////////////////////////////////////////////////////////

if ($action=='deletegroup')
{
    deleteSheetGroup(clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN));
} elseif ($action=='deletesheet') {
    deleteSheet(clean_param($_REQUEST['sheet'],PARAM_CLEAN));

    // if we came from the sheet group page, head back their after deleting
    if (clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN)) {
        include $action_table['editgroup'];
        exit;
    }
}

$sheets = getOwnedSheetsAndAssociatedInfo();

# if the user doesn't have a default sheet group, create one
if (! $sheets) // create group since none exists
{    
    // create an object for the insert_record function
    $new_group_array = newSheetGroup();
    $new_group_array[0]->name = preg_replace('/\'/',"''","$USER->firstname $USER->lastname").' signup-sheets';
    $new_group_array[0]->description = 'Main collection of signup-sheets created by '.preg_replace('/\'/',"''","$USER->firstname $USER->lastname");
    $new_group_array[0]->flag_is_default = 1;
 
    if (! insert_record('sus_sheetgroups', $new_group_array[0]))
    {
        error('Initial sheet group creation/insert failed.');
    }
    
    // re-run query (inefficient, but easy)
    $sheets = getOwnedSheetsAndAssociatedInfo();
}


# display the list
#   at the top of each group, show action to create a new sheet in that group
#   at the end of the list (or top of the list?) show action to create new group
#  !NOTE: clicking on a group takes user to editing page for that group
#  !NOTE: clicking on a sheet takes user to editing page for that sheet
#  !NOTE: for each group and sheet, show some (not yet sure what) info besides the name

$last_sg_id = 0;
$last_sg_is_default = 0;
echo "<ul class=\"sus_sheetgroup_list\">\n";

foreach ($sheets as $sheet){
    if ($sheet->sg_id != $last_sg_id)
    {
        $last_sg_is_default = $sheet->sg_flag_is_default;
	if ($last_sg_id != 0)  // close prior item, if not first pass
        {
            echo "      <li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
            echo "    </ul>\n";
            echo "  </li>\n"; 
        }
        $last_sg_id = $sheet->sg_id;
        echo "  <li class=\"sus_sheetgroup_item\" id=\"sus_sheetgroup_$last_sg_id\">";
        echo "<h3><a href=\"$edit_group_url$last_sg_id\">$sheet->sg_name</a>";
        if (! $last_sg_is_default){
            echo '  <a href="?sheetgroup='.$last_sg_id.'&action=deletegroup&contextid='.$context->id.'" onclick="return confirm(\'Any sheets in this group will be deleted.\n\nReally delete this group?\');" class="nukeit_link"><img src="image/pix/t/delete.png"/ title="delete sheet group" alt="delete sheet group" class="nukeit"></a>';
        }
	echo "</h3>\n";

        echo "    <ul class=\"sus_sheet_list\">\n";
    }
    if ($sheet->s_id)
    {
        echo "      <li class=\"sus_sheet_item\" id=\"sus_sheet_$sheet->s_id\">";
        echo '  <a href="?sheet='. $sheet->s_id. '&action=deletesheet&contextid=' .$context->id. '" onclick="return confirm(\'Really delete this sheet?\');" class="nukeit_link"><img src="image/pix/t/delete.png"/ title="delete sheet" alt="delete sheet" class="nukeit"></a>';        
	echo "<a href=\"$edit_sheet_url$sheet->s_id&sheetgroup=$sheet->sg_id\">$sheet->s_name</a>";

        echo "</li>\n";
    }
}
echo "      <li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
echo "    </ul>\n";
echo "  </li>\n";

echo "  <li class=\"sus_sheetgroup_item sus_new_sheetgroup\" id=\"new_sheetgroup\"><a href=\"$add_group_url\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new group</a></li>";
echo "</ul><!-- end sus_sheetgroup_list-->\n";

// get admin-ed sheets
// if there are any
//  list them in a basic, black-bordered white box
$admin_sheets = getAdminSheetsAndAssociatedInfo();
if ($admin_sheets)
{
    echo "<div class=\"sus_sheetgroup_item sus_admin_sheets\">Sheets I manage that are owned by others:\n";
    echo "  <ul class=\"sus_sheet_list\">\n";
    foreach ($admin_sheets as $as)
    {
        echo "    <li class=\"sus_sheet_item\" id=\"sus_sheet_$as->s_id\">";
        echo "<a href=\"$edit_sheet_url$as->s_id&sheetgroup=$as->sg_id\">$as->s_name</a>";
        echo " (owned by {$as->usr_firstname} {$as->usr_lastname})";
        echo "</li>\n";
    }
    echo "  </ul>\n</div>\n";
}
?>
</div>