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

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));

$tx_comments_path = t3lib_extMgm::extPath('comments');
require_once($tx_comments_path . 'view/class.tx_comments_error_view.php');
require_once($tx_comments_path . 'view/class.tx_comments_form_view.php');
require_once($tx_comments_path . 'view/class.tx_comments_comments_view.php');
unset($tx_comments_path);

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
	 * Language object
	 *
	 * @var	language
	 */
	protected $lang;

	/**
	 * Creates an instance of this class.
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->lang = t3lib_div::makeInstance('language');
		$this->lang->init($GLOBALS['TSFE']->lang);
	}

	/**
	 * Processes requests to this controller.
	 *
	 * @param	string	$content	Content (normally empty)
	 * @param	array	$conf	TypoScript configuration
	 * @return	string	Generated content
	 */
	public function main($content, array $conf) {
		$content = '';

		// Process configuration and check for errors
		$errors = $this->processConfiguration($conf);
		if (count($errors) > 0) {
			$content = $this->errorView($errors);
		}

		// Dispatch the request
		$content = $this->dispatchRequest();

		return $content;
	}

	/**
	 * Obtains content object for this plugin
	 *
	 * @return	tslib_cObj	Content object
	 */
	public function getCObj() {
		return $this->cObj;
	}

	/**
	 * Obtains configuration for this plugin
	 *
	 * @return	array	Configuration for this plugin
	 */
	public function getConfiguration() {
		return $this->conf;
	}

	/**
	 * Processes and verifies TypoScript configuration for this controller.
	 *
	 * @param	array	$conf	Configuration
	 * @return	array	Array with errors (empty array of no errors)
	 */
	protected function processConfiguration(array $conf) {
		$errors = array();

		// Check that TS for this extension is added to site's TS
		if (!isset($conf['templateFile'])) {
			// No TS template included
			$errors[] = $this->lang->sL('LLL:EXT:comments/pi1/locallang.xml:error.no.ts.template');
		}
		else {
			// Merge flexform and TS configurations
			$this->mergeConfiguration($conf);

			// Process&validate some values
		}
		return $errors;
	}

	/**
	 * Dispatches request using the mode from the configuration.
	 *
	 * @return	string	Generated content
	 */
	protected function dispatchRequest() {
		$modes = t3lib_div::trimExplode(',', $this->conf['code']);
		foreach ($modes as $mode) {
			switch($mode) {
				case 'FORM':
					$this->formView();
					break;
				case 'COMMENTS':
					$this->commentsView();
					break;
				default:

			}
		}
		return '';
	}

	/**
	 * Merges configuration from TypoScript
	 *
	 * @param	array	$conf	Configuration from TypoScript
	 * @return	void
	 */
	protected function mergeConfiguration(array $conf) {
		$flexformArray = $this->parseFlexform();
		if (count($flexformArray)) {
			// We have flexform configuration and need to merge it
			$this->conf = t3lib_div::array_merge_recursive_overrule($conf, $flexformArray);
		}
		else {
			// No flexform, so just assign TS configuration
			$this->conf = $conf;
		}
	}

	/**
	 * Parses flexform configuration into the TS-like array
	 *
	 * @return	array	Parsed flexform
	 */
	protected function parseFlexform() {
		$result = array();
		// Check if we were called as a content element from the page. If not,
		// flexform will not be set.
		if (isset($this->cObj->data['pi_flexform'])) {
			$flexArray = t3lib_div::xml2array($this->cObj->data['pi_flexform']);
			if (is_array($flexArray) && isset($flexArray['data'])) {
				foreach ($flexArray['data'] as $sheetName => $sheetData) {
					// Data must be an array too with 'lDEF' member as array
					if (is_array($sheetData) && isset($sheetData['lDEF']) && is_array($sheetData['lDEF'])) {
						// Convert sheet name
						if ($sheetName != 'sDEF') {
							// This is is not a 'sDEF' sheet, so entries will
							// go into a subarray
							if (preg_match('/^s[A-Z]/', $sheetName)) {
								$sectionName = strtolower(substr($sheetName, 1, 1)) .
									substr($sheetName, 2) . '.';
							}
							else {
								$sectionName = $sheetName . '.';
							}
							$result[$sectionName] = array();
							$array = &$result[$sectionName];
						}
						else {
							// For the sDEF sheet entries go directly to the $result
							$array = &$result;
						}
						// Go for values. We support only 'lDEF' due to the
						// <meta> definition in the flexform data source.
						foreach ($sheetData['lDEF'] as $field => $value) {
							$array[$field] = $value;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Shows errors for this plugin
	 *
	 * @param	array	$errors	Errors to display
	 * @return	string	Generated HTML
	 */
	protected function errorView(array $errors) {
		$errorViewClassName = t3lib_div::makeInstanceClassName('tx_comments_error_view');
		$errorView = new $errorViewClassName($this, $errors);
		/* @var $errorView tx_comments_error_view */
		return $errorView->render();
	}

	/**
	 * Shows form view for this plugin
	 *
	 * @return	string	Generated HTML
	 */
	protected function formView() {
		return 'form view here...';
	}

	/**
	 * Shows comments view for this plugin
	 *
	 * @return	string	Generated HTML
	 */
	protected function commentsView() {
		return 'comments view here...';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php']);
}

?>