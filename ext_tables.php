<?php
/* $Id$ */

if (!defined('TYPO3_MODE')) die('Access denied.');

// Add static files for plugins
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Comments');

// Add pi1 plugin
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
t3lib_extMgm::addPlugin(Array('LLL:EXT:comments/pi1/locallang.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');

t3lib_extMgm::addPiFlexFormValue($_EXTKEY .'_pi1', 'FILE:EXT:comments/pi1/flexform_ds.xml');


// Comments table
$TCA['tx_comments_comments'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments',
		'label' => 'content',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
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

// URL log table
$TCA['tx_comments_urllog'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_urllog',
		'label' => 'external_ref',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'sortby' => 'external_ref',
		'delete' => 'deleted',
		'hideTable' => true,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_urllog.gif',
	)
);
t3lib_extMgm::allowTableOnStandardPages('tx_comments_urllog');
t3lib_extMgm::addToInsertRecords('tx_comments_urllog');
t3lib_extMgm::addLLrefForTCAdescr('tx_comments_urllog', 'EXT:comments/locallang_csh.php');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_comments_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_comments_pi1_wizicon.php';
}

?>