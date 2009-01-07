<?php

########################################################################
# Extension Manager/Repository config file for ext: "comments"
#
# Auto generated 07-01-2009 12:02
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Commenting system',
	'description' => 'Adds commenting for pages or virtually any record visible in frontend. Public free support is provided only through TYPO3 mailing lists! Contact by e-mail for commercial support. Use bug tracker to report bugs!',
	'category' => 'plugin',
	'author' => 'Dmitry Dulepov [netcreators]',
	'author_email' => 'dmitry@typo3.org',
	'shy' => '',
	'dependencies' => 'pagebrowse',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'Netcreators BV',
	'version' => '1.5.2',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-100.0.0',
			'pagebrowse' => '1.0.0-100.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:37:{s:9:"ChangeLog";s:4:"df53";s:32:"class.tx_comments_cms_layout.php";s:4:"82c2";s:25:"class.tx_comments_eID.php";s:4:"bd78";s:29:"class.tx_comments_tcemain.php";s:4:"275a";s:28:"class.tx_comments_ttnews.php";s:4:"b05f";s:12:"ext_icon.gif";s:4:"7edf";s:17:"ext_localconf.php";s:4:"4d98";s:14:"ext_tables.php";s:4:"9003";s:14:"ext_tables.sql";s:4:"8557";s:22:"flexform_functions.php";s:4:"4db3";s:17:"icon_comments.gif";s:4:"07a5";s:30:"icon_comments_not_approved.gif";s:4:"1d20";s:15:"icon_urllog.gif";s:4:"0ad4";s:17:"locallang_csh.xml";s:4:"cfb6";s:16:"locallang_db.xml";s:4:"256e";s:17:"locallang_eID.xml";s:4:"ed77";s:19:"locallang_hooks.xml";s:4:"7e29";s:7:"tca.php";s:4:"854b";s:15:"csh/captcha.png";s:4:"41a5";s:14:"doc/manual.sxw";s:4:"2e19";s:14:"pi1/ce_wiz.gif";s:4:"c787";s:29:"pi1/class.tx_comments_pi1.php";s:4:"0611";s:37:"pi1/class.tx_comments_pi1_wizicon.php";s:4:"28e3";s:19:"pi1/flexform_ds.xml";s:4:"0040";s:28:"pi1/flexform_ds_advanced.xml";s:4:"374f";s:27:"pi1/flexform_ds_general.xml";s:4:"2cd5";s:31:"pi1/flexform_ds_spamprotect.xml";s:4:"d539";s:17:"pi1/locallang.xml";s:4:"1497";s:21:"pi1/locallang_csh.xml";s:4:"07b7";s:24:"pi1/static/constants.txt";s:4:"cad0";s:20:"pi1/static/setup.txt";s:4:"a53c";s:25:"res/commenting-closed.gif";s:4:"bd93";s:13:"res/email.txt";s:4:"12cf";s:27:"res/pagebrowser-correct.png";s:4:"7a12";s:11:"res/pi1.css";s:4:"d2c7";s:10:"res/pi1.js";s:4:"0b14";s:21:"res/pi1_template.html";s:4:"987b";}',
	'suggests' => array(
	),
);

?>