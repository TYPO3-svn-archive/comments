<?php
/* $Id$ */

/**
 * Checks if merged flexform file must be written to typo3temp.
 *
 * @param	string	$filePath	Path to merged file
 * @return	boolean	<code>true</code> if file has to be written
 */
function tx_comments_mustUpdateTempFlexFile($filePath) {
	if (!@file_exists($filePath)) {
		return true;
	}
	$mtime = @filemtime($filePath);
	foreach (array('general', 'advanced', 'spamprotect') as $pref) {
		if ($mtime < @filemtime(t3lib_extMgm::extPath('comments', 'pi1/flexform_ds_' . $pref . '.xml'))) {
			return true;
		}
	}
	return false;
}

/**
 * Makes workaround against a bug in pre-4.2 versions of typo3 where flexform sheet references caused fatal PHP error in PHP5.
 *
 * @return	string	Merged flexform file path
 */
function tx_comments_makeTempFlexFormDS() {
	$ffFileName = PATH_site . 'typo3temp/tx_comments_flexform_ds.xml';
	if (tx_comments_mustUpdateTempFlexFile($ffFileName)) {
		$ffContent = t3lib_div::getURL(t3lib_extMgm::extPath('comments', 'pi1/flexform_ds.xml'));
		$ds = t3lib_div::xml2array($ffContent);
		$sheets = t3lib_div::resolveAllSheetsInDS($ds);
		unset($ds['sheets']);
		$ds['sheets'] = $sheets['sheets'];
		$ffContentNew = t3lib_div::array2xml($ds, '', 0, 'T3DataStructure');
		t3lib_div::writeFileToTypo3tempDir($ffFileName, $ffContentNew);
	}
	return 'FILE:' . substr($ffFileName, strlen(PATH_site));
}

if (!defined('TYPO3_MODE')) die('Access denied.');

// Add static files for plugins
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/', 'Commenting system');

// Add pi1 plugin
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
t3lib_extMgm::addPlugin(Array('LLL:EXT:comments/pi1/locallang.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');
if (version_compare(TYPO3_version, '4.2', '<')) {
	// Pre-4.2 dies if flexform has references to sheets
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY .'_pi1', tx_comments_makeTempFlexFormDS());
}
else {
	// 4.2 or newer works fine with flexforms
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY .'_pi1', 'FILE:EXT:comments/pi1/flexform_ds.xml');
}

// Comments table
$TCA['tx_comments_comments'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments',
		'label' => 'content',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'sortby' => 'crdate',
		'default_sortby' => ' ORDER BY crdate DESC',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_comments.gif',
		//'type' => 'approved',
		'typeicon_column' => 'approved',
		'typeicons' => array(
			'0' => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_comments_not_approved.gif',
			'1' => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_comments.gif',
		),
	)
);
t3lib_extMgm::allowTableOnStandardPages('tx_comments_comments');
t3lib_extMgm::addToInsertRecords('tx_comments_comments');
t3lib_extMgm::addLLrefForTCAdescr('tx_comments_comments', 'EXT:comments/locallang_csh.php');

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_comments_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_comments_pi1_wizicon.php';
}
?>