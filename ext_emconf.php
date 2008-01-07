<?php

########################################################################
# Extension Manager/Repository config file for ext: "comments"
#
# Auto generated 07-01-2008 15:36
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Commenting system',
	'description' => 'Adds commenting for pages or virtually any record visible in frontend',
	'category' => 'plugin',
	'author' => 'Dmitry Dulepov [netcreators]',
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
	'author_company' => 'Netcreators BV',
	'version' => '1.0.7',
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
	'_md5_values_when_last_written' => 'a:34:{s:9:"ChangeLog";s:4:"4e58";s:32:"class.tx_comments_cms_layout.php";s:4:"58cf";s:25:"class.tx_comments_eID.php";s:4:"aa34";s:29:"class.tx_comments_tcemain.php";s:4:"fa5d";s:12:"ext_icon.gif";s:4:"7edf";s:17:"ext_localconf.php";s:4:"d5ce";s:14:"ext_tables.php";s:4:"dc92";s:14:"ext_tables.sql";s:4:"59e7";s:22:"flexform_functions.php";s:4:"4db3";s:17:"icon_comments.gif";s:4:"07a5";s:30:"icon_comments_not_approved.gif";s:4:"1d20";s:17:"locallang_csh.xml";s:4:"616e";s:16:"locallang_db.xml";s:4:"fd6d";s:17:"locallang_eID.xml";s:4:"ed77";s:7:"tca.php";s:4:"1e95";s:15:"csh/captcha.png";s:4:"41a5";s:12:"doc/TODO.txt";s:4:"27a3";s:14:"doc/manual.sxw";s:4:"0dcc";s:14:"pi1/ce_wiz.gif";s:4:"c787";s:29:"pi1/class.tx_comments_pi1.php";s:4:"b498";s:37:"pi1/class.tx_comments_pi1_wizicon.php";s:4:"28e3";s:19:"pi1/flexform_ds.xml";s:4:"a146";s:28:"pi1/flexform_ds_advanced.xml";s:4:"f244";s:27:"pi1/flexform_ds_general.xml";s:4:"a3c8";s:31:"pi1/flexform_ds_spamprotect.xml";s:4:"3b2b";s:17:"pi1/locallang.xml";s:4:"2299";s:21:"pi1/locallang_csh.xml";s:4:"3468";s:24:"pi1/static/constants.txt";s:4:"c428";s:20:"pi1/static/setup.txt";s:4:"1ee4";s:25:"res/commenting-closed.gif";s:4:"bd93";s:13:"res/email.txt";s:4:"12cf";s:11:"res/pi1.css";s:4:"e5c1";s:10:"res/pi1.js";s:4:"bd93";s:21:"res/pi1_template.html";s:4:"7390";}',
	'suggests' => array(
	),
);

?>