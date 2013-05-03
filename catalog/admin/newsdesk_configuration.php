<?php

require('includes/application_top.php');

if ($_GET['action']) {
	switch ($_GET['action']) {
	case 'save':
		$configuration_value = tep_db_prepare_input($_POST['configuration_value']);
		$cID = tep_db_prepare_input($_GET['cID']);

		tep_db_query(
		"update " . TABLE_NEWSDESK_CONFIGURATION . " set configuration_value = '" . tep_db_input($configuration_value) .
		"', last_modified = now() where configuration_id = '" . tep_db_input($cID) . "'"
		);

		tep_redirect(tep_href_link(FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cID));
		break;
	}
}

$cfg_group_query = tep_db_query(
"select configuration_group_title from " . TABLE_NEWSDESK_CONFIGURATION_GROUP . " where configuration_group_id = '" . $_GET['gID'] . "'"
);

$cfg_group = tep_db_fetch_array($cfg_group_query);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo TITLE; ?></title>
<link href="templates/admin/css/template_css.css" rel="stylesheet" type="text/css" />


<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
	<tr>
		<td width="<?php echo BOX_WIDTH; ?>" valign="top">
<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>

<!-- left_navigation_eof //-->
</table>
		</td>
<!-- body_text //-->

		<td width="100%" valign="top">
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td class="pageHeading"><?php echo $cfg_group['configuration_group_title']; ?></td>
		<td class="pageHeading2" align="right"></td>
	</tr>
</table>
		</td>
	</tr>
	<tr>
		<td>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr class="dataTableHeadingRow">
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
		<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
	</tr>

<?php

 $configuration_query = tep_db_query(
 "select configuration_id, configuration_title, configuration_value, use_function from " . TABLE_NEWSDESK_CONFIGURATION . " where configuration_group_id = '" . $_GET['gID'] . "' order by sort_order"
);

while ($configuration = tep_db_fetch_array($configuration_query)) {
	if (tep_not_null($configuration['use_function'])) {
		$use_function = $configuration['use_function'];
		if (preg_match('/->/', $use_function)) {
			$class_method = explode('->', $use_function);
			if (!is_object(${$class_method[0]})) {
				include(DIR_WS_CLASSES . $class_method[0] . '.php');
				${$class_method[0]} = new $class_method[0]();
			}
		$cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
		} else {
			$cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
		}
	} else {
		$cfgValue = $configuration['configuration_value'];
	}

	if (((!$_GET['cID']) || (@$_GET['cID'] == $configuration['configuration_id'])) && (!$cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {

		$cfg_extra_query = tep_db_query(
"select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_NEWSDESK_CONFIGURATION . " where configuration_id = '" . $configuration['configuration_id'] . "'"
);

		$cfg_extra = tep_db_fetch_array($cfg_extra_query);

		$cInfo_array = array_merge($configuration, $cfg_extra);
		$cInfo = new objectInfo($cInfo_array);
	}

	if ( (is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
		echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\''
		. tep_href_link(FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit')
		. '\'">' . "\n";
	} else {
		echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'"
		onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWSDESK_CONFIGURATION,
		'gID=' . $_GET['gID'] . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
	}
?>

		<td class="dataTableContent"><?php echo $configuration['configuration_title']; ?></td>
		<td class="dataTableContent"><?php echo htmlspecialchars($cfgValue); ?></td>
		<td class="dataTableContent" align="right">
<?php
if ( (is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
	echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.png', '');
} else {
	echo '<a href="' . tep_href_link(FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID='
	. $configuration['configuration_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
}
?>
		&nbsp;</td>
	</tr>

<?php
}
?>

</table>
		</td>

<?php
$heading = array();
$contents = array();
switch ($_GET['action']) {
case 'edit':
	$heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

	if ($cInfo->set_function) {
		eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
	} else {
			$value_field = tep_draw_input_field('configuration_value', $cInfo->configuration_value);
	}

	$contents = array(
	'form' => tep_draw_form('configuration', FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id
	. '&action=save')
	);

	$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
	$contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
	$contents[] = array(
		'align' => 'center',
		'text' => '<br>' . tep_image_submit('button_update.png', IMAGE_UPDATE) . '&nbsp;<a class="button" href="'
		. tep_href_link(FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id)
		. '">' .  IMAGE_CANCEL . '</a>'
		);
	break;

default:
if (is_object($cInfo)) {
	$heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

	$contents[] = array(
		'align' => 'center',
		'text' => '<a class="button" href="' . tep_href_link(FILENAME_NEWSDESK_CONFIGURATION, 'gID=' . $_GET['gID']
		. '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' .  IMAGE_EDIT . '</a>'
		);

	$contents[] = array('text' => '<br>' . $cInfo->configuration_description);
	$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
	if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
	}
	break;
}

if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
	echo '<td valign="top"  width="220px">' . "\n";

	$box = new box;
	echo $box->infoBox($heading, $contents);

	echo '</td>' . "\n";

}
?>

	</tr>
</table>
		</td>
	</tr>
</table>
		</td>
<!-- body_text_eof //-->
	</tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>

<?php
/*

	CartStore eCommerce Software, for The Next Generation ---- http://www.cartstore.com
	Copyright (c) 2008 Adoovo Inc. USA	GNU General Public License Compatible

	IMPORTANT NOTE:

	This script is not part of the official osC distribution but an add-on contributed to the osC community.
	Please read the NOTE and INSTALL documents that are provided with this file for further information and installation notes.

	script name:			NewsDesk
	version:        		1.48.2
	date:       			22-06-2004 (dd/mm/yyyy)
	author:				Carsten aka moyashi
	web site:			www..com
	modified code by:		Wolfen aka 241
*/
?>