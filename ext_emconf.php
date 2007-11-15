<?php

########################################################################
# Extension Manager/Repository config file for ext: "comments"
#
# Auto generated 07-11-2007 17:42
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Commenting system',
	'description' => 'Adds commenting for virtually any record visible in frontend. Created for Netcreators BV.',
	'category' => 'plugin',
	'author' => 'Dmitry Dulepov',
	'author_email' => 'dmitry@typo3.org',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => 'nc_commerce,nc_comments',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'http://typo3bloke.net/',
	'version' => '0.8.0',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
			'nc_commerce' => '',
			'nc_comments' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:29:{s:9:"ChangeLog";s:4:"27c7";s:32:"class.tx_comments_cms_layout.php";s:4:"58cf";s:25:"class.tx_comments_eID.php";s:4:"aa34";s:29:"class.tx_comments_tcemain.php";s:4:"fa5d";s:12:"ext_icon.gif";s:4:"7edf";s:17:"ext_localconf.php";s:4:"d5ce";s:14:"ext_tables.php";s:4:"e3b6";s:14:"ext_tables.sql";s:4:"e6bf";s:17:"icon_comments.gif";s:4:"07a5";s:30:"icon_comments_not_approved.gif";s:4:"1d20";s:17:"locallang_csh.xml";s:4:"616e";s:16:"locallang_db.xml";s:4:"7772";s:17:"locallang_eID.xml";s:4:"ed77";s:7:"tca.php";s:4:"5601";s:14:"doc/manual.sxw";s:4:"b642";s:29:"pi1/class.tx_comments_pi1.php";s:4:"3bb8";s:19:"pi1/flexform_ds.xml";s:4:"a146";s:28:"pi1/flexform_ds_advanced.xml";s:4:"b230";s:27:"pi1/flexform_ds_general.xml";s:4:"4c38";s:31:"pi1/flexform_ds_spamprotect.xml";s:4:"5270";s:17:"pi1/locallang.xml";s:4:"bf94";s:24:"pi1/static/constants.txt";s:4:"15c0";s:20:"pi1/static/setup.txt";s:4:"f373";s:25:"res/commenting-closed.gif";s:4:"10c8";s:13:"res/email.txt";s:4:"12cf";s:11:"res/pi1.css";s:4:"998e";s:10:"res/pi1.js";s:4:"bd93";s:21:"res/pi1_template.html";s:4:"955a";s:25:"res/tt_news_template.html";s:4:"9f00";}',
	'suggests' => array(
	),
);

?>