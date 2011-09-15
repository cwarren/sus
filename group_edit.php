<?php
if (! verify_in_signup_sheets()) { die("not in signup_sheets"); }

$DEBUG=0;

////////////////////////////////////////////////////////////////////////////////////////

$sheet_group_id = clean_param($_REQUEST['sheetgroup'],PARAM_CLEAN);

# !!! NOTE: sheet_group_id gets re-set from 'new' to 0 in here!
if ($sheet_group_id == 'new')
{
    $sheet_groups = newSheetGroup('sg_');
    $sheet_group_id = 0;
} else
{
    $sg_info = array(
        'name' => optional_param('name', 'no name provided'),
        'description' => optional_param('description', ''),
        'max_g_total_user_signups' => optional_param('max_total_signups', '-1'),
        'max_g_pending_user_signups' => optional_param('max_pending_signups', '-1'));

    if (isset($_REQUEST['subaction']))
    {
     if ($_REQUEST['subaction'] == 'updategroup')
        {
            updateSheetGroup($sheet_group_id,$sg_info);
        } elseif ($_REQUEST['subaction'] == 'creategroup')
        {
            $sheet_group_id = addSheetGroup($sg_info);
        }
    }


    $sheet_groups = getOwnedSheetsAndAssociatedInfo($sheet_group_id);

    if (! $sheet_groups) // no sheet_groups found, something went wrong
    {    
        error("failed to find sheet group $sheet_group_id for $USER->id");
    }
}
//$sheet = $sheets[$sheet_group_id];
$sheet = $sheet_groups[0];

debug_r(2,$sheet);

?>
<div id="sus_user_notify"></div>
<div id="sus_custom_alert"><h1 class="alert_title"></h1><div class="alert_text"></div><div><input type="button" value="close" id="custom_alert_close"/></div></div>
<div class="sus_sheetgroup_data">

  <?php
if ($sheet_group_id)
{
  ?>
  <p class="sus_timestamp_info">This group was created on <?php 
echo date('Y-n-j',$sheet->sg_created_at);
echo ' at '.date('g:i A',$sheet->sg_created_at);
if ($sheet->sg_created_at+5 < $sheet->sg_updated_at) { 
    echo ", and last changed on ".date('Y-n-j',$sheet->sg_updated_at);
    echo ' at '.date('g:i A',$sheet->sg_updated_at); 
}
  ?>.</p>
  <?php
}
  ?>

  <form action="" method="POST">
    <input type="hidden" name="contextid" value="<?php echo $context->id; ?>" />
    <input type="hidden" name="sheetgroup" value="<?php echo $sheet_group_id; ?>" />
    <input type="hidden" name="action" value="editgroup" />
    <input type="hidden" name="subaction" value="<?php echo ($sheet_group_id)?'updategroup':'creategroup'; ?>" />
    <div id="sheetgroup_name"><input id="input_sheetgroup_name" <?php echo $sheet_group_id?'':'class="sus_temp_data"';?> type="text" name="name" value="<?php cleanecho($sheet->sg_name,true);?>" maxlength="255" /></div>
    <div id="sheetgroup_description"><textarea id="text_sheetgroup_description" <?php echo $sheet_group_id?'':'class="sus_temp_data"';?> name="description"><?php cleanecho($sheet->sg_description,true);?></textarea></div>

    <div id="sheetgroup_max_signups" class="sus_formtext">Users can have at most 
<select name="max_total_signups" id="select_max_total">
  <option value="-1" <?php echo (($sheet->sg_max_g_total_user_signups < 0 || $sheet->sg_max_g_total_user_signups == '') ? 'selected="selected"' : ''); ?>>unlimited</option>
<?php echo getOptions($sheet->sg_max_g_total_user_signups,1,8); ?>
</select>
signup<?php echo (($sheet->sg_max_g_total_user_signups==1)?'':'s');?> across all sheets in this group, and 
<select name="max_pending_signups" id="select_max_pending">
  <option value="-1" <?php echo (($sheet->sg_max_g_pending_user_signups < 0 || $sheet->sg_max_g_pending_user_signups == '') ? 'selected="selected"' : ''); ?>>any</option>
<?php echo getOptions($sheet->sg_max_g_pending_user_signups,1,8); ?>
</select>
may be for future openings.</div>


    <div id="action_button_box">
      <input type="submit" class="sus_action_button_large" id="save_group_button" name="action_save" value="Save" />
      <input type="submit" class="sus_action_button_large" name="action_cancel" value="Cancel" />
<?php
    if ($sheet_group_id != 0 && (! $sheet->sg_flag_is_default))
    { 
        echo ' <span class="sus_action_button_large sus_delete_group"><a href="?action=deletegroup&contextid='.$context->id.'&sheetgroup='.$sheet->sg_id.'" onclick="return confirm(\'Really delete this group?\');"><img class="nukeit" src="image/pix/t/delete.png">DELETE GROUP</a></span>';
    }
    ?>
    </div>


  </form>
<script type="text/javascript">
document.getElementById("input_sheetgroup_name").focus();
document.getElementById("input_sheetgroup_name").select();

$(document).ready(function()
{

  // validate form
  $("#save_group_button").click(function(event) {
    if (! ($("#input_sheetgroup_name").val().match(/\S/)))
    {
      customAlert("Missing Information","You must enter a name for the group");
      $("#input_sheetgroup_name").focus();
      return false;
    }
    return true;    
  });

  $("#custom_alert_close").click(function(event)
  {
//    $("#sus_custom_alert").stop(true,true);
//    $("#sus_custom_alert").css("left","-999");
    $("#input_sheetgroup_name").focus();
  });

});

</script>

<?php 
if ($sheet_group_id != 'new')
{
?>
  <h3>Sheets in this group</h3>
  <ul class="sus_sheet_list">
<?php
# now list the sheets in this group, if any
$sheet_count = 0;
foreach ($sheet_groups as $sheet)
{
    if ($sheet->s_id)
    {
	$sheet_count++;
        echo "    <li class=\"sus_sheet_item\" id=\"sus_sheet_$sheet->s_id\"><a href=\"$edit_sheet_url$sheet->s_id&sheetgroup=$sheet->sg_id\">$sheet->s_name</a>";
        echo '<a href="?sheet='.$sheet->s_id.'&action=deletesheet&contextid='.$context->id.'&sheetgroup='.$sheet->sg_id.'" onclick="return confirm(\'Really delete this sheet?\');"><img src="image/pix/t/delete.png"/ title="delete sheet" alt="delete sheet" class="nukeit"></a>';
        echo "</li>\n";
    }
}
if ($sheet_count == 0) { echo "    <li class=\"sus_none\">none</li>\n"; }
echo "    <li class=\"sus_sheet_item sus_new_sheet_item\" id=\"new_sheet_for_$sheet_group_id\"><a href=\"$add_sheet_url$sheet_group_id\"><img src=\"image/pix/t/add.png\" class=\"addit\"/>Add a new sheet to this group</a></li>\n";
?>
  </ul>
<?php 
} // end if ($sheet_group_id != 'new')
?>

</div><!-- end sus_sheetgroup_data -->