<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2009 Dmitry Dulepov <dmitry@typo3.org>
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
 * This class implements the main Frontend controller for the comments extension
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
class tx_comments_fecontroller {

	/**
	 * Content object for this class. This is set by tslib_cObj::callUserFunc()
	 * directly after creating the instance of this class.
	 *
	 * @var	tslib_cObj
	 */
	public $cObj;

	/**
	 * Configuration of this plugin instance
	 *
	 * @var	array
	 */
	protected $conf = array();

	/**
	 * Processes requests to this controller.
	 *
	 * @param	string	$content	Content (normally empty)
	 * @param	array	$conf	TypoScript configuration
	 * @return	string	Generated content
	 */
	public function main($content, array $conf) {
		$content = '';

		$errors = $this->processConfiguration($conf);

		return $content;
	}

	/**
	 * Processes and verifies TypoScript configuration for this controller.
	 *
	 * @param	array	$conf	Configuration
	 * @return	array	Array with errors (empty array of no errors)
	 */
	protected function processConfiguration(array $conf) {
		$errors = array();

		return $errors;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php']);
}

?>