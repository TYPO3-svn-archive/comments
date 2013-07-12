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
 * $Id$
 */


/**
 * This class implements the main Frontend controller for the comments extension
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
class tx_comments_fecontroller extends tx_comments_basecontroller {

	/**
	 * Submitted comment
	 *
	 * @var	tx_comments_comment
	 */
	protected $submittedComment = null;

	/**
	 * Form validation results
	 *
	 * @var	array
	 */
	protected $formValidationResults = array();

	/**
	 * Post data for this plugin
	 *
	 * @var	array
	 */
	protected $postData = array();

	/**
	 * Select key for comment records. Typically it looks like 'tt_news_20'.
	 *
	 * @var	string
	 */
	protected $selectKey = '';

	/**
	 * Processes requests to this controller.
	 *
	 * @param	string	$content	Content (normally empty)
	 * @param	array	$conf	TypoScript configuration
	 * @return	string	Generated content
	 */
	public function main($content, array $conf) {
		$content = '';

		$this->fetchPostData();

		// Process configuration and check for errors
		$errors = $this->processConfiguration($conf);
		if (count($errors) > 0) {
			$content = $this->errorView($errors);
		}
		else {
			// Dispatch the request
			$content = $this->dispatchRequest();
		}
		return $content;
	}

	/**
	 * Retrieves form validation results.
	 *
	 * @return	array	Form validation results. Key is field, value is error message
	 */
	public function getFormValidationResults() {
		return $this->formValidationResults;
	}

	/**
	 * Checks if form submission happened
	 *
	 * @return	boolean	true if the form was submitted
	 */
	public function isFormSubmitted() {
		return (count($this->postData) > 0);
	}

	/**
	 * Obtains plugin's post data
	 *
	 * @return	void
	 */
	protected function fetchPostData() {
		// TODO Use the name according to the compatibility mode?
		$data = t3lib_div::_POST('tx_comments');
		if (!is_array($data)) {
			$data = (array)t3lib_div::_POST('tx_comments_pi1');
		}
		$this->postData = $data;
	}

	/**
	 * Retrieves submitted comment
	 *
	 * @return	tx_comments_comment	Submitted comment or null if nothing was submitted
	 */
	public function getSubmittedComment() {
		return $this->submittedComment;
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
		if (t3lib_div::inList($modes, 'FORM')) {
			$this->processForm();
		}
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
		$errorView = t3lib_div::makeInstance('tx_comments_error_view', $this, $errors);
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

	/**
	 * Processes form submission. The result is in the $this->submittedComment.
	 *
	 * @return	void
	 */
	protected function processForm() {
		$postData = $this->getPostData();

		if (count($postData) > 0) {
			// Check that submitted data is valid and it is for this instance
			$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
			if ($postData['itemurlchk'] == md5($url . $this->cObj->data['uid'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
				$this->submittedComment = t3lib_div::makeInstance('tx_comments_comment');

				$this->submittedComment->setPid($this->conf['storagePid']);
				$this->submittedComment->setFirstName((string)$postData['firstname']);
				$this->submittedComment->setLastName((string)$postData['lastname']);
				$this->submittedComment->setEmail((string)$postData['email']);
				$this->submittedComment->setHomePage((string)$postData['homepage']);
				$this->submittedComment->setLocation((string)$postData['location']);
				$this->submittedComment->setContent((string)$postData['content']);

				// TODO Captcha check

				// TODO Call a hook to set custom fields

				if ($this->submittedComment->validate($this->conf['requiredFields'])) {
					// TODO Spam check
					// TODO Pre-save hook
					$this->submittedComment->save();
					// TODO Post-save hook
				}
			}
		}
	}

	/**
	 * Creates a select key from the configuration (externalPrefix) and current
	 * value passed as GET or POST var
	 *
	 * @return	void
	 */
	protected function createSelectKey() {
		if ($this->conf['externalPrefix']) {
			if ($this->conf['externalPrefix'] != 'pages') {
				if ($this->conf['showUidMap.'][$this->conf['externalPrefix']]) {
					$showUidParam = $this->conf['showUidMap.'][$this->conf['externalPrefix']];

					$ar = t3lib_div::_GP($this->conf['externalPrefix']);
					$id = (is_array($ar) ? intval($ar[$showUidParam]) : false);
					if ($id) {
						$this->selectKey = $this->conf['externalPrefix'] . '_' . $id;
					}
				}
			}
			else {
				$this->selectKey = 'pages_' . $GLOBALS['TSFE']->id;
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/controller/class.tx_comments_fecontroller.php']);
}

?>