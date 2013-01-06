<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Dmitry Dulepov <dmitry@typo3.org>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * $Id$
 */


/**
 * This class implements a base data store. It performs most typical operations
 * for the data store.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
abstract class tx_comments_basedatastore {

	/**
	 * Table name for this store. This attribute should be set in the
	 * dereived class constructor.
	 *
	 * @var	string
	 */
	protected $tableName = null;

	/**
	 * Class name that is a data class for this store. This must be set by the
	 * derieved class constructor.
	 *
	 * @var	string
	 */
	protected $dataClass = null;

	/**
	 * Retrieves data instance by its id
	 *
	 * @param	int	$id	uid of the data
	 * @return	tx_comments_basemodel	Data class instance or null if not found
	 */
	public function getById($id) {
		$items = $this->get('uid=' . intval($id));
		return (count($items) > 0 ? $items[0] : null);
	}

	/**
	 * Generic function to retrieve data from the current table using an SQL
	 * condition. An example condition is:
	 * 	myfield=12345
	 *
	 * @param	string	$condition	Condition
	 * @param	string	$sortby	Expression for the 'ORDER BY' (without 'ORDER BY')
	 * @package	string	$limit	Number of items or start/stop range (like in SQL LIMIT statement)
	 * @return	array	Array of tx_comments_basemodel
	 */
	public function get($condition, $sortby = '', $limit = '') {
		$result = array();

		if ($this->tableName == null) {
			trigger_error('tx_comments_basedatastore: $this->tableName is null in ' .
				'get(). Did you forget to set it in the constructor of ' .
				get_class($this) . '?');
		}
		elseif ($this->dataClass == null) {
			trigger_error('tx_comments_basedatastore: $this->dataClass is null in ' .
				'get(). Did you forget to set it in the constructor of ' .
				get_class($this) . '?');
		}
		else {
			$className = t3lib_div::makeInstance($this->dataClass);
			if (!$className) {
				trigger_error('tx_comments_basedatastore: t3lib_div::makeInstance(\'' .
					$this->dataClass . '\') failed in get()');
			}
			else {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',
					$this->tableName, $condition . $this->enableFields($this->tableName),
					'', $sortby, $limit);
				if ($res) {
					// Get class name of the data
					while (false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
						$result[] = t3lib_div::makeInstance($row);
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}
			}
		}
		return $result;
	}

	/**
	 * Creates 'enableFields' SQL statement for the given table
	 *
	 * @param	string	$table	Table name
	 * @return	string	Additional where statement (starts with ' AND')
	 */
	protected function enableFields($table) {
		if (TYPO3_MODE == 'BE') {
			$enableFieds1 = t3lib_BEfunc::BEenableFields($table);
			if (strtoupper(trim($enableFieds1)) == 'AND') {
				$enableFieds = '';
			}
			$enableFieds2 = t3lib_BEfunc::deleteClause($table);
			if (strtoupper(trim($enableFieds2)) == 'AND') {
				$enableFieds = '';
			}
			$enableFieds = $enableFieds1 . $enableFieds2;
		}
		else {
			$enableFieds = $GLOBALS['TSFE']->sys_page->enableFields($table);
		}
		return $enableFieds;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/model/class.tx_comments_basedatastore.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/model/class.tx_comments_basedatastore.php']);
}

?>