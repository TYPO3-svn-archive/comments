<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Dmitry Dulepov <dmitry@typo3.org>
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
 * $Id: $
 */


/**
 * This class implements an abstract model for the comments extension.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */

abstract class tx_comments_basemodel {

	/**
	 * Original database row
	 *
	 * @var	array
	 */
	protected	$originalRow;

	/**
	 * Current database row
	 *
	 * @var	array
	 */
	protected	$currentRow;

	/**
	 * Table name for this model
	 *
	 * @var	string
	 */
	protected	$tableName = null;

	/**
	 * Creates an instance of this class. Dereived classes must call this
	 * constructor and set $this->tableName in their constructor.
	 *
	 * @param array $row
	 */
	public function __construct(array $row = array()) {
		$this->originalRow = $this->currentRow = $row;
	}

	/**
	 * Retrieves ID of the comment record
	 *
	 * @return	int	ID of the record
	 */
	public function getId() {
		return $this->updatedRow['id'];
	}

	/**
	 * Retrieves comment record page id
	 *
	 * @return	int	Page id
	 */
	public function getPid() {
		return $this->updatedRow['pid'];
	}

	/**
	 * Sets page id of the record
	 *
	 * @param	int	$pid	Page id of the record
	 * @return	void
	 */
	public function setPid($pid) {
		$this->updatedRow['pid'] = $pid;
	}

	/**
	 * Saves the current model. This method takes care of executing insert or
	 * update query when necessary.
	 *
	 * @return	boolean	true if successful
	 */
	public function save() {
		$result = false;
		// Chekc that table is set
		if ($this->tableName == null) {
			trigger_error('tx_comments_basemodel: $this->tableName is null. ' .
				'Did you forget to set it in the derieved constructor?', E_USER_ERROR);
		}
		else {
			// See if we have any changes and what operation we should perform
			$fields = array_diff_assoc($this->currentRow, $this->originalRow);
			if (count($fields) > 0) {
				// Ok, we have some new or updated fields. What do we do: insert or update?
				if (isset($this->originalRow['uid'])) {
					// We have uid in the original row. So we do update.
					if (isset($fields['uid'])) {
						unset($fields['uid']);
					}
					$fields['tstamp'] = time();
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName,
						'uid=' . $this->originalRow['uid'], $fields);
					$result = ($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0);
				}
				else {
					$fields['crdate'] = $fields['tstamp'] = time();
					$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableName, $fields);
					if ($GLOBALS['TYPO3_DB']->sql_error() == '') {
						$newRecordId = $GLOBALS['TYPO3_DB']->sql_insert_id();
						list($this->currentRow) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',
							$this->tableName, 'uid=' . $newRecordId);
						$result = true;
					}
				}
			}
		}
		if ($result) {
			// Make sure that current and original data is now identical
			$this->originalRow = $this->currentRow;
		}
		return $result;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/model/class.tx_comments_basemodel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/model/class.tx_comments_basemodel.php']);
}

?>