<?php

########################################################################
# Extension Manager/Repository config file for ext "comments".
#
# Auto generated 19-04-2011 17:27
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Comments',
	'description' => 'Adds commenting functionality for pages or virtually any record visible in frontend.',
	'category' => 'plugin',
	'author' => 'Ingo Renner',
	'author_email' => 'ingo@typo3.org',
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
	'author_company' => '',
	'version' => '1.7.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.1.0-',
			'php' => '5.3.0-',
			'pagebrowse' => '1.3.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:46:{s:9:"ChangeLog";s:4:"0be9";s:32:"class.tx_comments_cms_layout.php";s:4:"82c2";s:25:"class.tx_comments_eID.php";s:4:"3b09";s:29:"class.tx_comments_tcemain.php";s:4:"0834";s:28:"class.tx_comments_ttnews.php";s:4:"e41c";s:12:"ext_icon.gif";s:4:"7edf";s:17:"ext_localconf.php";s:4:"0718";s:14:"ext_tables.php";s:4:"08f6";s:14:"ext_tables.sql";s:4:"8557";s:22:"flexform_functions.php";s:4:"4db3";s:17:"icon_comments.gif";s:4:"07a5";s:30:"icon_comments_not_approved.gif";s:4:"1d20";s:15:"icon_urllog.gif";s:4:"0ad4";s:17:"locallang_csh.xml";s:4:"cfb6";s:16:"locallang_db.xml";s:4:"256e";s:17:"locallang_eID.xml";s:4:"ed77";s:19:"locallang_hooks.xml";s:4:"7e29";s:7:"tca.php";s:4:"b166";s:47:"controller/class.tx_comments_basecontroller.php";s:4:"48ce";s:45:"controller/class.tx_comments_fecontroller.php";s:4:"56f4";s:15:"csh/captcha.png";s:4:"41a5";s:14:"doc/manual.sxw";s:4:"2e19";s:41:"model/class.tx_comments_basedatastore.php";s:4:"4902";s:37:"model/class.tx_comments_basemodel.php";s:4:"b5c2";s:35:"model/class.tx_comments_comment.php";s:4:"6ea5";s:45:"model/class.tx_comments_comment_datastore.php";s:4:"8475";s:14:"pi1/ce_wiz.gif";s:4:"c787";s:29:"pi1/class.tx_comments_pi1.php";s:4:"4b9b";s:37:"pi1/class.tx_comments_pi1_wizicon.php";s:4:"28e3";s:19:"pi1/flexform_ds.xml";s:4:"0040";s:28:"pi1/flexform_ds_advanced.xml";s:4:"374f";s:27:"pi1/flexform_ds_general.xml";s:4:"9ec4";s:31:"pi1/flexform_ds_spamprotect.xml";s:4:"ac5e";s:17:"pi1/locallang.xml";s:4:"f62b";s:21:"pi1/locallang_csh.xml";s:4:"ea40";s:40:"resources/template/commenting-closed.gif";s:4:"bd93";s:28:"resources/template/email.txt";s:4:"12cf";s:26:"resources/template/pi1.css";s:4:"ffe7";s:25:"resources/template/pi1.js";s:4:"0b14";s:36:"resources/template/pi1_template.html";s:4:"449a";s:20:"static/constants.txt";s:4:"4273";s:16:"static/setup.txt";s:4:"25ba";s:35:"view/class.tx_comments_baseview.php";s:4:"12e6";s:36:"view/class.tx_comments_errorview.php";s:4:"f586";s:35:"view/class.tx_comments_formview.php";s:4:"c72b";s:35:"view/class.tx_comments_listview.php";s:4:"ba8f";}',
	'suggests' => array(
	),
);

?>