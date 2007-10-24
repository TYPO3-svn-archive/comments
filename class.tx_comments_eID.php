<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov (dmitry@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* class.tx_comments_pi1.php
*
* Comment management script.
*
* $Id$
*
* @author Dmitry Dulepov <dmitry@typo3.org>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   60: class tx_comments_eID
 *   64:     function init()
 *  100:     function main()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
require_once(PATH_site . 't3lib/class.t3lib_tcemain.php');

/**
 * Comment management script.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage tx_comments
 */
class tx_comments_eID {
	var $uid;
	var $command;

	function init() {
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init('default');
		$GLOBALS['LANG']->includeLLFile('EXT:comments/locallang_eID.xml');

		tslib_eidtools::connectDB();

		// Sanity check
		$this->uid = t3lib_div::_GET('uid');
		if (!t3lib_div::testInt($this->uid)) {
			echo $GLOBALS['LANG']->getLL('bad_uid_value');
			exit;
		}
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_comments_comments', 'uid=' . $this->uid);
		if ($rows[0]['t'] == 0) {
			echo $GLOBALS['LANG']->getLL('comment_does_not_exist');
			exit;
		}

		$check = t3lib_div::_GET('chk');
		if (md5($this->uid . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) != $check) {
			echo $GLOBALS['LANG']->getLL('wrong_check_value');
			exit;
		}
		$this->command = t3lib_div::_GET('cmd');
		if (!t3lib_div::inList('approve,delete,kill', $this->command)) {
			echo $GLOBALS['LANG']->getLL('wrong_cmd');
			exit;
		}
	}

	/**
	 * Main processing function of eID script
	 *
	 * @return	void
	 */
	function main() {
		switch ($this->command) {
			case 'approve':
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_comments_comments', 'uid=' . $this->uid, array('approved' => 1));
				echo $GLOBALS['LANG']->getLL('comment_approved');
				break;
			case 'delete':
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_comments_comments', 'uid=' . $this->uid, array('deleted' => 1));
				echo $GLOBALS['LANG']->getLL('commeted_deleted');
				break;
			case 'kill':
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_comments_comments', 'uid=' . $this->uid);
				echo $GLOBALS['LANG']->getLL('commented_killed');
				break;
		}
		// Clear cache. TCEmain requires $TCA for this, so we just do it ourselves.
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT(pid)', 'tt_content', 'list_type=\'comments_pi1\' AND deleted=0 AND hidden=0');
		$pids = array();
		foreach ($pages as $pid) {
			$pids[] = $pid['pid'];
		}
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'page_id IN ('.implode(',', $pids).')');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pagesection', 'page_id IN ('.implode(',', $pids).')');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/class.tx_comments_eID.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/class.tx_comments_eID.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_comments_eID');
$SOBE->init();
$SOBE->main();
?>