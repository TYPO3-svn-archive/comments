<?php
/* $Id$ */

if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_comments_pi1.php', '_pi1', 'list_type', 1);

// TCEmain hook to remove comments if referenced item is removed
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['comments'] = 'EXT:comments/class.tx_comments_tcemain.php:tx_comments_tcemain';

// Page module hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['comments_pi1'][] = 'EXT:comments/class.tx_comments_cms_layout.php:tx_comments_cms_layout->getExtensionSummary';

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['comments'] = 'EXT:comments/class.tx_comments_eID.php';

// Extra markers hook for tt_news
if (t3lib_extMgm::isLoaded('tt_news')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][$_EXTKEY] = 'EXT:comments/class.tx_comments_ttnews.php:&tx_comments_ttnews';
}
?>