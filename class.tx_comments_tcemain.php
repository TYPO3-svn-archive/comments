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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Handling of related records for comments.
 *
 * $Id$
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_comments_tcemain
 *   61:     function processCmdmap_postProcess($command, $table, $id, $value, &$pObj)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * A hook to TCEmain to remove comments if associated record is removed.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage comments
 */
class tx_comments_tcemain {

	/**
	 * Removes all references to comments if associated record is removed
	 *
	 * @param	string		$command	Command. We are interested only in 'delete'
	 * @param	string		$table	Table name.
	 * @param	int		$id	Record uid
	 * @param	mixed		$value	Unused
	 * @param	t3lib_TCEmain		$pObj	Reference to parent object
	 * @return	void		Nothing
	 */
	function processCmdmap_postProcess($command, $table, $id, $value, &$pObj) {
		/* @var $pObj t3lib_TCEmain */
		if ($command == 'delete' && $table != 'tx_comments_comments') {
			$external_ref = $table . '_' . $id;
			// Note: disabling mysql query cache for this query because it is executed only once
			//		and there is no need to flood cache with such queries!
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('/*! SQL_NO_CACHE */ uid', 'tx_comments_comments',
						'external_ref=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($external_ref, 'tx_comments_comments') .
						t3lib_BEfunc::deleteClause('tx_comments_comments'));
			$cmdmap = array();
			foreach ($rows as $row) {
				$cmdmap['tx_comments_comments'][$row['uid']]['delete'] = true;
			}
			if (count($cmdmap)) {
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				/* @var $tce t3lib_TCEmain */
				$tce->start(false, $cmdmap, $pObj->BE_USER);
				$tce->process_cmdmap();
				if (count($tce->errorLog)) {
					$pObj->errorLog = array_merge($pObj->errorLog, $tce->errorLog);
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/class.tx_comments_tcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/class.tx_comments_tcemain.php']);
}

?>