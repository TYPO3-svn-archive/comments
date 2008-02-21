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
* Commenting system.
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
 *   87: class tx_comments_pi1 extends tslib_pibase
 *  118:     function main($content, $conf)
 *  165:     function init()
 *  212:     function mergeConfiguration()
 *  278:     function fetchConfigValue($param)
 *  298:     function checkExternalUid()
 *  313:     function comments()
 *  340:     function comments_getComments(&$rows)
 *  369:     function comments_getComments_getRatings(&$row)
 *  389:     function comments_getComments_getEmail($email)
 *  404:     function comments_getPageBrowser($page, $rpp, $rowCount)
 *  476:     function comments_getPageBrowser_getPageLink($page)
 *  493:     function form()
 *  565:     function form_updatePostVarsWithFeUserData(&$postVars)
 *  612:     function form_getCaptcha()
 *  646:     function form_wrapError($field)
 *  656:     function processSubmission()
 *  758:     function processSubmission_checkTypicalSpam()
 *  800:     function processSubmission_validate()
 *  846:     function sendNotificationEmail($uid, $points)
 *  878:     function isCommentingClosed()
 *  927:     function commentingClosed()
 *  942:     function formatDate($date)
 *  957:     function fixLL()
 *  991:     function createLinks($text)
 *
 * TOTAL FUNCTIONS: 24
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'class.t3lib_refindex.php');
require_once(PATH_tslib . 'class.tslib_pibase.php');

if (t3lib_extMgm::isLoaded('ratings')) {
	require_once(t3lib_extMgm::extPath('ratings', 'class.tx_ratings_api.php'));
}

/**
 * Commenting system for tt_products.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage tx_comments
 */
class tx_comments_pi1 extends tslib_pibase {
	// Default plugin variables:
	var $prefixId = 'tx_comments_pi1';
	var $scriptRelPath = 'pi1/class.tx_comments_pi1.php';
	var $extKey = 'comments';
	var $pi_checkCHash = true;				// Required for proper caching! See in the typo3/sysext/cms/tslib/class.tslib_pibase.php

//	var $conf;								// Plugin configuration (merged with flexform)
	var $externalUid;						// UID of external record
	var $showUidParam = 'showUid';			// Name of 'showUid' GET parameter (different for tt_news!)
	var $where;								// SQL WHERE for records
	var $where_dpck;						// SQL WHERE for double post checks
	var $templateCode;						// Full template code
	var $foreignTableName;					// Table name of the record we comment on
	var $formValidationErrors = array();	// Array of form validation errors
	var $formTopMessage = '';				// This message is displayed in the top of the form

	/**
	 * Ratings API
	 *
	 * @var	tx_ratings_api
	 */
	var $ratingsApiObj = null;

	/**
	 * Main function of the plugin
	 *
	 * @param	string		$content	Content (unused)
	 * @param	array		$conf	TS configuration of the extension
	 * @return	void
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->fixLL();
		$this->pi_loadLL();

		// Check if TS template was included
		if (!isset($conf['prefixToTableMap.'])) {
			// TS template is not included
			return $this->pi_wrapInBaseClass($this->pi_getLL('error.no.ts.template'));
		}

		// Initialize
		$this->init();
		if (!$this->foreignTableName) {
			return sprintf($this->pi_getLL('error.undefined.foreign.table'), $this->prefixId, $this->conf['externalPrefix']);
		}

		$content = '';

		// check if we need to go at all
		if ($this->checkExternalUid()) {
			switch ($this->conf['code']) {
				case 'COMMENTS':
					$content = $this->comments();
					break;
				case 'FORM':
					if ($this->isCommentingClosed()) {
						$content = $this->commentingClosed();
					}
					else {
						// check form submission
						$this->processSubmission();
						$content = $this->form();
					}
					break;
				default:
					$content = $this->checkCustomFunctionCodes();
					break;
			}
			$content = $this->pi_wrapInBaseClass($content);
		}
		return $content;
	}

	/**
	 * Initializes the plugin
	 *
	 * @param	array		$conf	Configuration from TS
	 * @return	void
	 */
	function init() {
		$this->mergeConfiguration();

		// See what we are commenting on
		if ($this->conf['externalPrefix'] != 'pages') {
			// Adjust 'showUid' for old extensions like tt_news
			if ($this->conf['showUidMap.'][$this->conf['externalPrefix']]) {
				$this->showUidParam = $this->conf['showUidMap.'][$this->conf['externalPrefix']];
			}

			$ar = t3lib_div::_GP($this->conf['externalPrefix']);
			$this->externalUid = (is_array($ar) ? intval($ar[$this->showUidParam]) : false);
			$this->foreignTableName = $this->conf['prefixToTableMap.'][$this->conf['externalPrefix']];
		}
		else {
			// We are commenting on page
			$this->externalUid = $GLOBALS['TSFE']->id;
			$this->foreignTableName = 'pages';
			$this->showUidParam = '';
		}

		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		$key = 'EXT:comments_' . md5($this->templateCode);
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
			$headerParts = $this->cObj->getSubpart($this->templateCode, '###HEADER_ADDITIONS###');
			if ($headerParts) {
				$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath('comments'));
				$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
			}
		}

		$this->where_dpck = 'external_prefix=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->conf['externalPrefix'], 'tx_comments_comments') .
					' AND external_ref=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->foreignTableName . '_' . $this->externalUid, 'tx_comments_comments') .
					' AND pid=' . intval($this->conf['storagePid']) .
					 $this->cObj->enableFields('tx_comments_comments');
		$this->where = 'approved=1 AND ' . $this->where_dpck;

		if ($this->conf['advanced.']['enableRatings'] && t3lib_extMgm::isLoaded('ratings')) {
			$this->ratingsApiObj = t3lib_div::makeInstance('tx_ratings_api');
		}
	}

	/**
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @return	void
	 */
	function mergeConfiguration() {
		$this->pi_initPIflexForm();

		$this->fetchConfigValue('code');
		$this->fetchConfigValue('storagePid');
		$this->fetchConfigValue('externalPrefix');
		$this->fetchConfigValue('templateFile');
		$this->fetchConfigValue('advanced.commentsPerPage');
		$this->fetchConfigValue('advanced.closeCommentsAfter');
		$this->fetchConfigValue('advanced.dateFormat');
		$this->fetchConfigValue('advanced.dateFormatMode');
		$this->fetchConfigValue('advanced.enableRatings');
		$this->fetchConfigValue('advanced.autoConvertLinks');
		$this->fetchConfigValue('advanced.enableUrlLog');
		$this->fetchConfigValue('spamProtect.requireApproval');
		$this->fetchConfigValue('spamProtect.useCaptcha');
		$this->fetchConfigValue('spamProtect.checkTypicalSpam');
		$this->fetchConfigValue('spamProtect.considerReferer');
		$this->fetchConfigValue('spamProtect.notificationEmail');
		$this->fetchConfigValue('spamProtect.fromEmail');
		$this->fetchConfigValue('spamProtect.emailTemplate');
		$this->fetchConfigValue('spamProtect.spamCutOffPoint');

		// Post process some values
		if ($this->conf['code'] == 'FORM') {
			$value = trim($this->conf['advanced.']['closeCommentsAfter']);
			if ($value != '') {
				switch ($value{strlen($value) - 1}) {
					case 'h':
						$suffix = 'hour';
						break;
					case 'm':
						$suffix = 'month';
						break;
					case 'y':
						$suffix = 'year';
						break;
					case 'd':
					default:
						$suffix = 'day';
						break;
				}
				$value = intval($value);
				if ($value > 1) {
					$suffix .= 's';
				}
				$this->conf['advanced.']['closeCommentsAfter'] = '+ ' . $value . ' ' . $suffix;
			}
		}
		// If storage pid is not set, use current page
		$this->conf['storagePid'] = intval($this->conf['storagePid']);
		if ($this->conf['storagePid'] == 0) {
			$this->conf['storagePid'] = $GLOBALS['TSFE']->id;
		}
		// Set date
		if (trim($this->conf['advanced.']['dateFormat']) == '') {
			$this->conf['advanced.']['dateFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
			$this->conf['dateFormatMode'] = 'date';
		}
		// Call hook for custom configuration
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['mergeConfiguration'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['mergeConfiguration'] as $userFunc) {
				$params = array(
					'pObj' => &$this,
				);
				t3lib_div::callUserFunction($userFunc, $params, $this);
			}
		}
	}

	/**
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param	string		$param	Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return	void
	 */
	function fetchConfigValue($param) {
		if (strchr($param, '.')) {
			list($section, $param) = explode('.', $param, 2);
		}
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, ($section ? 's' . ucfirst($section) : 'sDEF')));
		if (!is_null($value) && $value != '') {
			if ($section) {
				$this->conf[$section . '.'][$param] = $value;
			}
			else {
				$this->conf[$param] = $value;
			}
		}
	}

	/**
	 * Checks that $this->externalUid represents a real record.
	 *
	 * @return	boolean		true, if $this->externalUid is ok
	 */
	function checkExternalUid() {
		$result = ($this->conf['externalPrefix'] == 'pages');
		if (!$result && $this->externalUid) {
			// Check other tables
			list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', $this->foreignTableName,
						'uid=' . intval($this->externalUid) . $this->cObj->enableFields($this->foreignTableName));
			$result = ($row['t'] == 1);
		}
		return $result;
	}

	/**
	 * Returns formatted comments.
	 *
	 * @return	string		Formatted comments
	 */
	function comments() {
		// Find starting record
		$page = max(1, intval($this->piVars['page']));
		$rpp = intval($this->conf['advanced.']['commentsPerPage']);
		$start = $rpp*($page - 1);

		// Get records
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,approved,crdate,firstname,lastname,homepage,location,email,content',
					'tx_comments_comments', $this->where, '', 'crdate', $start . ',' . $rpp);

		$markerArray = array(
			'###SINGLE_COMMENT###' => $this->comments_getComments($rows),
			'###PAGE_BROWSER###' => $this->comments_getPageBrowser($page, $rpp, count($rows)),
			'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
			'###UID###' => $this->externalUid,
		);
		// Call hook for custom markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['comments'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['comments'] as $userFunc) {
				$params = array(
					'pObj' => &$this,
					'template' => $template,
					'markers' => $markerArray,
				);
				if (is_array($tempMarkers = t3lib_div::callUserFunction($userFunc, $params, $this))) {
					$markerArray = $tempMarkers;
				}
			}
		}

		$template = $this->cObj->getSubpart($this->templateCode, '###COMMENT_LIST###');
		return $this->cObj->substituteMarkerArrayCached($template, array(), $markerArray);
	}

	/**
	 * Generates list of comments
	 *
	 * @param	array		$rows	Rows from tx_comments_comments
	 * @return	string		Generated HTML
	 */
	function comments_getComments(&$rows) {
		$entries = array(); $alt = 1;
		$template = $this->cObj->getSubpart($this->templateCode, '###SINGLE_COMMENT###');
		foreach ($rows as $row) {
			$markerArray = array(
				'###ALTERNATE###' => '-' . ($alt + 1),
				'###FIRSTNAME###' => $this->applyStdWrap(htmlspecialchars($row['firstname']), 'firstName_stdWrap'),
				'###LASTNAME###' => $this->applyStdWrap(htmlspecialchars($row['lastname']), 'lastName_stdWrap'),
				'###EMAIL###' => $this->applyStdWrap($this->comments_getComments_getEmail($row['email']), 'email_stdWrap'),
				'###LOCATION###' => $this->applyStdWrap(htmlspecialchars($row['location']), 'location_stdWrap'),
				'###HOMEPAGE###' => $this->applyStdWrap(htmlspecialchars($row['homepage']), 'webSite_stdWrap'),
				'###COMMENT_DATE###' => $this->formatDate($row['crdate']),
				'###COMMENT_CONTENT###' => $this->applyStdWrap(nl2br($this->createLinks(htmlspecialchars($row['content']))), 'content_stdWrap'),
				'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
				'###RATINGS###' => $this->comments_getComments_getRatings($row),
			);
			// Call hook for custom markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['comments_getComments'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['comments_getComments'] as $userFunc) {
					$params = array(
						'pObj' => &$this,
						'template' => $template,
						'markers' => $markerArray,
					);
					if (is_array($tempMarkers = t3lib_div::callUserFunction($userFunc, $params, $this))) {
						$markerArray = $tempMarkers;
					}
				}
			}
			$entries[] = $this->cObj->substituteMarkerArray($template, $markerArray);
			$alt = ($alt + 1) % 2;
		}

		return implode('', $entries);
	}

	/**
	 * Retrieves ratings for this comment.
	 *
	 * @param	array		$row	Comment row data
	 * @return	string		Ratings	HTML for this row
	 */
	function comments_getComments_getRatings(&$row) {
		if ($this->ratingsApiObj) {
			$conf = $this->conf['ratingsConfig.'];
			if (!is_array($conf)) {
				$conf = $this->ratingsApiObj->getDefaultConfig();
			}
			if ($this->isCommentingClosed()) {
				$conf['mode'] = 'static';
			}
			return $this->ratingsApiObj->getRatingDisplay('tx_comments_comments_' . $row['uid'], $conf);
		}
		return '';
	}

	/**
	 * Generates e-mail taking spam protection into account
	 *
	 * @param	string		$email	E-mail
	 * @return	string		Generated e-mail code
	 */
	function comments_getComments_getEmail($email) {
		return ($email ? $this->cObj->typoLink_URL(array(
					'parameter' => $email,
					))
				: '');
	}

	/**
	 * Creates a page browser
	 *
	 * @param	int		$page	Page numer
	 * @param	int		$rpp	Record per page
	 * @param	int		$rowCount	Numer of rown on the current page
	 * @return	string		Generated HTML
	 */
	function comments_getPageBrowser($page, $rpp, $rowCount) {
		$haveNext = $haveLast = false;
		if ($rowCount == $rpp) {
			// Possibly next page
			list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('(COUNT(*)-' . ($page*$rpp) . ') AS t',
					'tx_comments_comments', $this->where);
			$haveLast = ($info['t'] > $rpp);
			$haveNext = ($info['t'] > 0);
			$lastPage = $page + intval($info['t']/$rpp) + (($info['t'] % $rpp) ? 1 : 0);
		}
		$haveFirst = ($page > 2);
		$havePrev = ($page > 1);

		$sectionArray = array();

		$template = $this->cObj->getSubpart($this->templateCode, '###PAGE_BROWSER###');

		if (!$havePrev && !$haveNext) {
			return '';
		}

		if ($haveFirst) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_FIRST_WRAP###');
			$sectionArray['###LINK_FIRST_WRAP###'] = $this->cObj->substituteMarkerArray($subTemplate, array(
								'###LINK_FIRST###' => $this->comments_getPageBrowser_getPageLink(1),
								'###TEXT_FIRST###' => $this->pi_getLL('pi1_template.first'),
							));
		}
		else {
			$sectionArray['###LINK_FIRST_WRAP###'] = '';
		}
		if ($havePrev) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_PREV_WRAP###');
			$sectionArray['###LINK_PREV_WRAP###'] = $this->cObj->substituteMarkerArray($subTemplate, array(
								'###LINK_PREV###' => $this->comments_getPageBrowser_getPageLink($page - 1),
								'###TEXT_PREVIOUS###' => $this->pi_getLL('pi1_template.previous'),
							));
		}
		else {
			$sectionArray['###LINK_PREV_WRAP###'] = '';
		}
		if ($haveNext) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_NEXT_WRAP###');
			$sectionArray['###LINK_NEXT_WRAP###'] = $this->cObj->substituteMarkerArray($subTemplate, array(
								'###LINK_NEXT###' => $this->comments_getPageBrowser_getPageLink($page + 1),
								'###TEXT_NEXT###' => $this->pi_getLL('pi1_template.next'),
							));
		}
		else {
			$sectionArray['###LINK_NEXT_WRAP###'] = '';
		}
		if ($haveLast) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_LAST_WRAP###');
			$sectionArray['###LINK_LAST_WRAP###'] = $this->cObj->substituteMarkerArray($subTemplate, array(
								'###LINK_LAST###' => $this->comments_getPageBrowser_getPageLink($lastPage),
								'###TEXT_LAST###' => $this->pi_getLL('pi1_template.last'),
							));
		}
		else {
			$sectionArray['###LINK_LAST_WRAP###'] = '';
		}

		return $this->cObj->substituteMarkerArrayCached($template, array('###CUR_PAGE###' => $page), $sectionArray);
	}

	/**
	 * Generates page link. Keeps all current URL parameters except for cHash and tx_comments_pi1[page].
	 *
	 * @param	int		$page	Page number starting from 1
	 * @return	string		Generated link
	 */
	function comments_getPageBrowser_getPageLink($page) {
		return $this->cObj->typoLink_URL(array(
			'parameter' => $GLOBALS['TSFE']->id,
			'addQueryString' => 1,
			'addQueryString.' => array(
				'exclude' => $this->prefixId . '[page],cHash,no_cache',
			),
			'additionalParams' => '&' . $this->prefixId . '[page]=' . $page,
			'useCacheHash' => true,
		));
	}

	/**
	 * Returns form to add a comment.
	 *
	 * @return	string		Formatted form
	 */
	function form() {
		$template = $this->cObj->getSubpart($this->templateCode, '###COMMENT_FORM###');
		$actionLink = $this->cObj->typoLink_URL(array(
			'parameter' => $GLOBALS['TSFE']->id,
			'addQueryString' => 1,
			'addQueryString.' => array(
				'exclude' => 'cHash,no_cache',
			),
			'additionalParams' => '&no_cache=1',
			'useCacheHash' => false,
		));
		$requiredFields = t3lib_div::trimExplode(',', $this->conf['requiredFields'], true);
		$requiredMark = $this->cObj->getSubpart($this->templateCode, '##REQUIRED_FIELD###');
		// We specially use _POST here instead of piVars. Do not change!
		$postVars = ($GLOBALS['TSFE']->no_cache ? t3lib_div::_POST($this->prefixId) : array());
		if (count($this->formValidationErrors['content']) == 0) {
			unset($postVars['content']);
		}
		if ($this->conf['advanced.']['preFillFormFromFeUser']) {
			$this->form_updatePostVarsWithFeUserData($postVars);
		}
		$itemUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		$markers = array(
			'###CURRENT_URL###' => htmlspecialchars($itemUrl),
			'###CURRENT_URL_CHK###' => md5($itemUrl . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']),
			'###TOP_MESSAGE###' => $this->formTopMessage,
			'###ACTION_URL###' => htmlspecialchars($actionLink),	// this must go before ##ACTION_URL### for proper replacement!
			'##ACTION_URL###' => htmlspecialchars($actionLink),	// compatibility with previous versions
			'###FIRSTNAME###' => htmlspecialchars($postVars['firstname']),
			'###LASTNAME###' => htmlspecialchars($postVars['lastname']),
			'###EMAIL###' => htmlspecialchars($postVars['email']),
			'###LOCATION###' => htmlspecialchars($postVars['location']),
			'###HOMEPAGE###' => htmlspecialchars($postVars['homepage']),
			'###CAPTCHA###' => $this->form_getCaptcha(),
			'###CONTENT###' => htmlspecialchars($postVars['content']),
			'###JS_USER_DATA###' => $postVars['submit'] ? '' : '<script type="text/javascript">tx_comments_pi1_setUserData()</script>',

			'###ERROR_FIRSTNAME###' => $this->form_wrapError('firstname'),
			'###ERROR_LASTNAME###' => $this->form_wrapError('lastname'),
			'###ERROR_EMAIL###' => $this->form_wrapError('email'),
			'###ERROR_LOCATION###' => $this->form_wrapError('location'),
			'###ERROR_HOMEPAGE###' => $this->form_wrapError('homepage'),
			'###ERROR_CONTENT###' => $this->form_wrapError('content'),

			'###REQUIRED_FIRSTNAME###' => in_array('firstname', $requiredFields) ? $requiredMark : '',
			'###REQUIRED_LASTNAME###' => in_array('lastname', $requiredFields) ? $requiredMark : '',
			'###REQUIRED_EMAIL###' => in_array('email', $requiredFields) ? $requiredMark : '',
			'###REQUIRED_LOCATION###' => in_array('location', $requiredFields) ? $requiredMark : '',
			'###REQUIRED_HOMEPAGE###' => in_array('homepage', $requiredFields) ? $requiredMark : '',
			'###REQUIRED_CONTENT###' => in_array('content', $requiredFields) ? $requiredMark : '',

			'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),

			'###TEXT_ADD_COMMENT###' => $this->pi_getLL('pi1_template.add_comment'),
			'###TEXT_REQUIRED_HINT###' => $this->pi_getLL('pi1_template.required_field'),
			'###TEXT_FIRST_NAME###' => $this->pi_getLL('pi1_template.first_name'),
			'###TEXT_LAST_NAME###' => $this->pi_getLL('pi1_template.last_name'),
			'###TEXT_EMAIL###' => $this->pi_getLL('pi1_template.email'),
			'###TEXT_WEB_SITE###' => $this->pi_getLL('pi1_template.web_site'),
			'###TEXT_LOCATION###' => $this->pi_getLL('pi1_template.location'),
			'###TEXT_CONTENT###' => $this->pi_getLL('pi1_template.content'),
			'###TEXT_SUBMIT###' => $this->pi_getLL('pi1_template.submit'),
			'###TEXT_RESET###' => $this->pi_getLL('pi1_template.reset'),
			'###TEXT_LOCATION###' => $this->pi_getLL('pi1_template.location'),
			'###TEXT_LOCATION###' => $this->pi_getLL('pi1_template.location'),
			'###TEXT_LOCATION###' => $this->pi_getLL('pi1_template.location'),
		);
		// Call hook for custom markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['form'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['form'] as $userFunc) {
				$params = array(
					'pObj' => &$this,
					'template' => $template,
					'markers' => $markers,
				);
				if (is_array($tempMarkers = t3lib_div::callUserFunction($userFunc, $params, $this))) {
					$markers = $tempMarkers;
				}
			}
		}
		return $this->cObj->substituteMarkerArray($template, $markers);
	}


	/**
	 * Examines $postVars and fills missing fields with FE user data.
	 *
	 * @param	array		$postVars	Data as submitted by form (can be empty array). Passed by reference and modified directly.
	 * @return	void
	 */
	function form_updatePostVarsWithFeUserData(&$postVars) {
		global $TSFE;

		if ($TSFE->fe_user->user['uid']) {
			$hasExtendedData = t3lib_extMgm::isLoaded('sr_feuser_register');
			// Notice: we check for sr_feuser_register and not for the existence of columns
			// in the record. This is intentional because if sr_feuser_register is removed,
			// columns will remain in database but may contain outdated values. So we use
			// these values only if we can assume they are updatable.
			if (!$postVars['firstname']) {
				if ($hasExtendedData && $TSFE->fe_user->user['first_name']) {
					$postVars['firstname'] = $TSFE->fe_user->user['first_name'];
				}
				else {
					$postVars['firstname'] = $TSFE->fe_user->user['name'];
				}
			}
			if (!$postVars['lastname']) {
				if ($hasExtendedData && $TSFE->fe_user->user['last_name']) {
					$postVars['lastname'] = $TSFE->fe_user->user['last_name'];
				}
			}
			if (!$postVars['email']) {
				$postVars['email'] = $TSFE->fe_user->user['email'];
			}
			if (!$postVars['location']) {
				$data = array();
				if ($TSFE->fe_user->user['city']) {
					$data[] = $TSFE->fe_user->user['city'];
				}
				if ($TSFE->fe_user->user['country']) {
					$data[] = $TSFE->fe_user->user['country'];
				}
				$postVars['location'] = implode(', ', $data);
				unset($data);
			}
			if (!$postVars['homepage']) {
				$postVars['homepage'] = $TSFE->fe_user->user['www'];
			}
		}
	}

	/**
	 * Adds captcha code if enabled.
	 *
	 * @return	string		Generated HTML
	 */
	function form_getCaptcha() {
		$captchaType = intval($this->conf['spamProtect.']['useCaptcha']);
		if ($captchaType == 1 && t3lib_extMgm::isLoaded('captcha')) {
			$template = $this->cObj->getSubpart($this->templateCode, '###CAPTCHA_SUB###');
			$code = $this->cObj->substituteMarkerArray($template, array(
							'###SR_FREECAP_IMAGE###' => '<img src="' . t3lib_extMgm::siteRelPath('captcha') . 'captcha/captcha.php" alt="" />',
							'###SR_FREECAP_CANT_READ###' => '',
							'###REQUIRED_CAPTCHA###' => $this->cObj->getSubpart($this->templateCode, '###REQUIRED_FIELD###'),
							'###ERROR_CAPTCHA###' => $this->form_wrapError('captcha'),
							'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
							'###TEXT_ENTER_CODE###' => $this->pi_getLL('pi1_template.enter_code'),
						));
			return str_replace('<br /><br />', '<br />', $code);
		}
		elseif ($captchaType == 2 && t3lib_extMgm::isLoaded('sr_freecap')) {
			require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
			$freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
			/* @var $freeCap tx_srfreecap_pi2 */
			$template = $this->cObj->getSubpart($this->templateCode, '###CAPTCHA_SUB###');
			return $this->cObj->substituteMarkerArray($template, array_merge($freeCap->makeCaptcha(), array(
							'###REQUIRED_CAPTCHA###' => $this->cObj->getSubpart($this->templateCode, '###REQUIRED_FIELD###'),
							'###ERROR_CAPTCHA###' => $this->form_wrapError('captcha'),
							'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments')
						)));
		}
		return '';
	}

	/**
	 * Wraps error message for the given field if error exists.
	 *
	 * @param	string		$field	Input field from the form
	 * @return	string		Error wrapped with stdWrap or empty string
	 */
	function form_wrapError($field) {
		return $this->formValidationErrors[$field] ?
					$this->cObj->stdWrap($this->formValidationErrors[$field], $this->conf['requiredFields_errorWrap.']) : '';
	}

	/**
	 * Processes form submissions.
	 *
	 * @return	void
	 */
	function processSubmission() {
		if ($this->piVars['submit'] && $this->processSubmission_validate()) {
			$external_ref = $this->foreignTableName . '_' . $this->externalUid;
			// Create record
			$record = array(
				'pid' => intval($this->conf['storagePid']),
				'external_ref' => $external_ref,	// t3lib_loaddbgroup should be used but it is very complicated for FE... So we just do it with brute force.
				'external_prefix' => trim($this->conf['externalPrefix']),
				'firstname' => trim($this->piVars['firstname']),
				'lastname' => trim($this->piVars['lastname']),
				'email' => trim($this->piVars['email']),
				'location' => trim($this->piVars['location']),
				'homepage' => trim($this->piVars['homepage']),
				'content' => trim($this->piVars['content']),
				'remote_addr' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
			);

			// Check for double post
			$double_post_check = md5(implode(',', $record));
			if ($this->conf['preventDuplicatePosts']) {
				list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_comments_comments',
						$this->where_dpck . ' AND crdate>=' . (time() - 60*60) . ' AND double_post_check=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($double_post_check, 'tx_comments_comments'));
			}
			else {
				$info = array('t' => 0);
			}

			if ($info['t'] > 0) {
				// Double post!
				$this->formTopMessage = $this->pi_getLL('error.double.post');
			}
			else {
				$isSpam = $this->processSubmission_checkTypicalSpam();
				$cutOffPoint = $this->conf['spamProtect.']['spamCutOffPoint'] ? $this->conf['spamProtect.']['spamCutOffPoint'] : $isSpam + 1;
				if ($isSpam < $cutOffPoint) {
					$isApproved = !$isSpam && intval($this->conf['spamProtect.']['requireApproval'] ? 0 : 1);

					// Add rest of the fields
					$record['crdate'] = $record['tstamp'] = time();
					$record['approved'] = $isApproved;
					$record['double_post_check'] = $double_post_check;

					// Insert comment record
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_comments_comments', $record);
					$dbError = ($GLOBALS['TYPO3_DB']->sql_error() != '');
					$newUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

					// Update reference index. This will show in theList view that someone refers to external record.
					$refindex = t3lib_div::makeInstance('t3lib_refindex');
					/* @var $refindex t3lib_refindex */
					$refindex->updateRefIndexTable('tx_comments_comments', $newUid);

					// Insert URL (if exists)
					if ($this->conf['advanced.']['enableUrlLog'] && $this->hasValidItemUrl()) {
						// See if exists
						$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,url', 'tx_comments_urllog',
										'external_ref=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($external_ref, 'tx_comments_urllog') .
										$this->cObj->enableFields('tx_comments_urllog'));
						if (count($rows) == 0) {
							$record = array(
								'crdate' => time(),
								'tstamp' => time(),
								'pid' => intval($this->conf['storagePid']),
								'external_ref' => $external_ref,
								'url' => $this->piVars['itemurl'],
							);
							$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_comments_urllog', $record);
							$refindex->updateRefIndexTable('tx_comments_urllog', $GLOBALS['TYPO3_DB']->sql_insert_id());
						}
						elseif ($rows[0]['url'] != $this->piVars['itemurl'] && !$this->isNoCacheUrl($this->piVars['itemurl'])) {
							$record = array(
								'tstamp' => time(),
								'url' => $this->piVars['itemurl'],
							);
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_comments_urllog', 'uid=' . $rows[0]['uid'], $record);
						}
					}

					// Set cookies
					foreach (array('firstname', 'lastname', 'email', 'location', 'homepage') as $field) {
						setcookie($this->prefixId . '_' . $field, $this->piVars[$field], time() + 365*24*60*60, '/');
					}

					// See what to do next
					if (!$isApproved) {
						// Show message
						$this->formTopMessage = $this->pi_getLL('requires.approval');
						$this->sendNotificationEmail($newUid, $isSpam);
					}
					else {
						// Clear cache
						$clearCache = t3lib_div::trimExplode(',', $this->conf['additionalClearCachePages'], true);
						$clearCache[] = $GLOBALS['TSFE']->id;
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						/* @var $tce t3lib_TCEmain */
						foreach (array_unique($clearCache) as $pid) {
							$tce->clear_cacheCmd($pid);
						}

						// Go to last page using redirect
						$rpp = intval($this->conf['advanced.']['commentsPerPage']);
						list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t',
								'tx_comments_comments', $this->where);
						$redirectLink = $this->cObj->typoLink_URL(array(
							'parameter' => $GLOBALS['TSFE']->id,
							'addQueryString' => 1,
							'addQueryString.' => array(
								'exclude' => $this->prefixId . '[page],cHash,no_cache',
							),
							'additionalParams' => '&no_cache=1&' . $this->prefixId . '[page]=' . (intval($info['t']/$rpp) + (($info['t'] % $rpp) ? 1 : 0)),
							'useCacheHash' => true,
						));
						@ob_end_clean();
						header('Location: ' . t3lib_div::locationHeaderUrl($redirectLink));
						exit;
					}
				}
				else {
					// Spam cut off point reached
					$this->formTopMessage = $this->pi_getLL('error_too_many_spam_points');
				}
			}
		}
		if ($this->formTopMessage) {
			$this->formTopMessage = $this->cObj->substituteMarkerArray(
				$this->cObj->getSubpart($this->templateCode, '###FORM_TOP_MESSAGE###'), array(
					'###MESSAGE###' => $this->formTopMessage,
					'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments')
				)
			);
		}
	}

	/**
	 * Checks for typical spam scenarios
	 *
	 * @return	int		Number of points. Considered as spam if more than zero
	 */
	function processSubmission_checkTypicalSpam() {
		$points = 0;

		if ($this->conf['spamProtect.']['checkTypicalSpam']) {
			// Typical BB-style spam: "[url="
			$points += intval(count(explode('[url=', $this->piVars['content']))/3);

			// Many links
			$points += intval(count(explode('http://', $this->piVars['content']))/10);

			// \n in the fields where it cannot appear due to form definition
			foreach (array('firstname', 'lastname', 'email', 'homepage', 'location') as $key) {
				$points += (strpos($this->piVars[$key], chr(10)) !== false ? 1 : 0);
				if ($key != 'homepage') {
					$points += (strpos($this->piVars[$key], 'http://') !== false ? 1 : 0);
				}
			}

			// Check referer - not reliable because firewals block it or browsers may forget to send it
			if ($this->conf['considerReferer']) {
				$parts1 = parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
				$parts2 = parse_url(t3lib_div::getIndpEnv('HTTP_HOST'));
				$points += ($parts1['host'] != $parts2['host']);
			}
		}

		// External spam checkers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['externalSpamCheck'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['externalSpamCheck'] as $_funcRef) {
				$params = array(
					'pObj' => &$this,
					'formdata' => $this->piVars,
					'points' => $points,
				);
				$points += t3lib_div::callUserFunction($_funcRef, $params, $this);
			}
		}

		return $points;
	}

	/**
	 * Validates submitted form. Errors are collected in <code>$this->formValidationErrors</code>
	 *
	 * @return	boolean		true, if form is ok.
	 */
	function processSubmission_validate() {
		// trim all
		foreach ($this->piVars as $key => $value) {
			$this->piVars[$key] = trim($value);
		}
		// Check required fields first
		$requiredFields = t3lib_div::trimExplode(',', $this->conf['requiredFields'], true);
		foreach ($requiredFields as $field) {
			if (!$this->piVars[$field]) {
				$this->formValidationErrors[$field] = $this->pi_getLL('error.required.field');
			}
		}
		// Validate e-mail
		if ($this->piVars['email'] && !t3lib_div::validEmail($this->piVars['email'])) {
			$this->formValidationErrors['email'] = $this->pi_getLL('error.invalid.email');
		}

		// Check spam: captcha
		$captchaType = intval($this->conf['spamProtect.']['useCaptcha']);
		if ($captchaType == 1 && t3lib_extMgm::isLoaded('captcha')) {
			@session_start();	// As of PHP 4.3.3, calling session_start() while the session has already been started will result in an error of level E_NOTICE. Also, the second session start will simply be ignored.
			$captchaStr = $_SESSION['tx_captcha_string'];
			$_SESSION['tx_captcha_string'] = '';
			if (!$captchaStr || $this->piVars['captcha'] !== $captchaStr) {
				$this->formValidationErrors['captcha'] = $this->pi_getLL('error.wrong.captcha');
			}
		}
		elseif ($captchaType == 2 && t3lib_extMgm::isLoaded('sr_freecap')) {
			require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
			$freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
			/* @var $freeCap tx_srfreecap_pi2 */
			if (!$freeCap->checkWord($this->piVars['captcha'])) {
				$this->formValidationErrors['captcha'] = $this->pi_getLL('error.wrong.captcha');
			}
		}

		return (count($this->formValidationErrors) == 0);
	}

	/**
	 * Sends notification e-mail about new comment
	 *
	 * @param	int		$uid	UID of new comment
	 * @param	int		$points	Number of earned spam points
	 * @return	void
	 */
	function sendNotificationEmail($uid, $points) {
		$toEmail = $this->conf['spamProtect.']['notificationEmail'];
		$fromEmail = $this->conf['spamProtect.']['fromEmail'];
		if (t3lib_div::validEmail($toEmail) && t3lib_div::validEmail($fromEmail)) {
			$template = $this->cObj->fileResource($this->conf['spamProtect.']['emailTemplate']);
			$check = md5($uid . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
			$markers = array(
				'###URL###' => t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
				'###POINTS###' => $points,
				'###FIRSTNAME###' => $this->piVars['firstname'],
				'###LASTNAME###' => $this->piVars['lastname'],
				'###EMAIL###' => $this->piVars['email'],
				'###LOCATION###' => $this->piVars['location'],
				'###HOMEPAGE###' => $this->piVars['homepage'],
				'###CONTENT###' => $this->piVars['content'],
				'###REMOTE_ADDR###' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
				'###APPROVE_LINK###' => t3lib_div::locationHeaderUrl('/index.php?eID=comments&uid=' . $uid . '&chk=' . $check . '&cmd=approve'),
				'###DELETE_LINK###' => t3lib_div::locationHeaderUrl('/index.php?eID=comments&uid=' . $uid . '&chk=' . $check . '&cmd=delete'),
				'###KILL_LINK###' => t3lib_div::locationHeaderUrl('/index.php?eID=comments&uid=' . $uid . '&chk=' . $check . '&cmd=kill'),
				'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
			);
			// Call hook for custom markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['sendNotificationMail'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['sendNotificationMail'] as $userFunc) {
					$params = array(
						'pObj' => &$this,
						'template' => $template,
						'check' => $check,
						'markers' => $markers,
					);
					if (is_array($tempMarkers = t3lib_div::callUserFunction($userFunc, $params, $this))) {
						$markers = $tempMarkers;
					}
				}
			}
			$content = $this->cObj->substituteMarkerArray($template, $markers);
			t3lib_div::plainMailEncoded($toEmail, $this->pi_getLL('email.subject'), $content, 'From: ' . $this->conf['spamProtect.']['fromEmail']);
		}
	}

	/**
	 * Checks if commenting is closed for this item
	 *
	 * @return	boolean		<code>true</code> if commenting is closed
	 */
	function isCommentingClosed() {
		// See if there are any hooks
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['closeCommentsAfter'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['closeCommentsAfter'] as $userFunc) {
				$params = array(
					'pObj' => &$this,
					'table' => $this->foreignTableName,
					'uid' => $this->externalUid,
				);

				$time = t3lib_div::callUserFunction($userFunc, $params, $this);
				if ($time !== false && t3lib_div::testInt($time)) {
					$time = intval($time);
					if ($time <= $GLOBALS['EXEC_TIME']) {
						return true;	// Commenting closed
					}
					// Expire this page cache when comments will be closed
					$GLOBALS['TSFE']->set_cache_timeout_default($time - $GLOBALS['EXEC_TIME']);
					return false;
				}
			}
		}

		// Try global settings
		$timeAdd = $this->conf['advanced.']['closeCommentsAfter'];
		if ($timeAdd == '') {
			// No time limit emposed
			return false;
		}
		t3lib_div::loadTCA($this->foreignTableName);
		if (isset($GLOBALS['TCA'][$this->foreignTableName]['ctrl']['crdate'])) {
			$fieldName = $GLOBALS['TCA'][$this->foreignTableName]['ctrl']['crdate'];
		}
		elseif (isset($GLOBALS['TCA'][$this->foreignTableName]['ctrl']['tstamp'])) {
			$fieldName = $GLOBALS['TCA'][$this->foreignTableName]['ctrl']['tstamp'];
		}
		else {
			// No time field configured in TCA -- cannot limit!
			return false;
		}
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fieldName, $this->foreignTableName,
					'uid=' . intval($this->externalUid) . $this->cObj->enableFields($this->foreignTableName));
		if (count($rows) == 1) {
			$time = strtotime($timeAdd, $rows[0][$fieldName]);
			if ($time <= $GLOBALS['EXEC_TIME']) {
				return true;
			}
			$GLOBALS['TSFE']->set_cache_timeout_default($time - $GLOBALS['EXEC_TIME']);
		}
		return false;
	}

	/**
	 * Produces "commenting closed" message.
	 *
	 * @return	void
	 */
	function commentingClosed() {
		$template = $this->cObj->getSubpart($this->templateCode, '###COMMENTING_CLOSED###');
		return $this->cObj->substituteMarkerArray($template, array(
						'###MESSAGE###' => $this->pi_getLL('commenting.closed'),
						'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments')
					)
				);
	}

	/**
	 * Formats date according to user's preferences
	 *
	 * @param	int		$date	Date as Unix timestamp
	 * @return	string		Formatted date
	 */
	function formatDate($date) {
		return ($this->conf['dateFormatMode'] == 'strftime' ?
			strftime($this->conf['dateFormat'], $date) :
			date($this->conf['advanced.']['dateFormat'], $date));
	}

	/**
	 * This function is workaround for a bug {@link http://bugs.typo3.org/view.php?id=7154 #7154}.
	 * This plugin uses dot characters in labels, this causes problems when someone
	 * tries to override labels from TS setup. It is possible to fix this by changing dots
	 * to underscopes but this will invalidate all translations + any existing TS template.
	 * Thus this functions converts array back to dotted string.
	 *
	 * @return	void
	 */
	function fixLL() {
		if (isset($this->conf['_LOCAL_LANG.'])) {
			// Walk each language
			foreach ($this->conf['_LOCAL_LANG.'] as $lang => $LL) {
				// If any label is set...
				if (count($LL)) {
					$ll = array();
					// Walk all labels
					foreach ($LL as $key => $value) {
						if (!is_array($value)) {
							// Not array
							$ll[$key] = $value;
						}
						// Convert array to plain string
						$segments = $key;
						while (is_array($value)) {
							$segments .= key($value);
							$value = current($value);
						}
						$ll[$segments] = $value;
					}
					// Set it back to configration
					$this->conf['_LOCAL_LANG.'][$lang] = $ll;
				}
			}
		}
	}

	/**
	 * Creates links from "http://..." or "www...." phrases.
	 *
	 * @param	string		$text	Text to search for links
	 * @return	string		Text to convert
	 */
	function createLinks($text) {
		return $this->conf['advanced.']['autoConvertLinks'] ?
			preg_replace('/((http:\/\/)?((?(2)([^\s]+)|(www\.[^\s]+))))/', '<a href="http://\3" rel="nofollow" class="tx-comments-external-autolink">\1</a>', $text) :
			$text;
	}

	/**
	 * Applies stdWrap to given text
	 *
	 * @param	string	$text	Text to apply stdWrap to
	 * @param	string	$stdWrapName	Name for the stdWrap in $this->conf
	 * @return	string	Wrapped text
	 */
	function applyStdWrap($text, $stdWrapName) {
		if (is_array($this->conf[$stdWrapName . '.'])) {
			$text = $this->cObj->stdWrap($text, $this->conf[$stdWrapName . '.']);
		}
		return $text;
	}

	/**
	 * Checks and processes custom function codes.
	 *
	 * @param	string	$code	Code
	 * @return	string	HTML code
	 */
	function checkCustomFunctionCodes($code) {
		// Call hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['customFunctionCode'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['customFunctionCode'] as $userFunc) {
				$params = array(
					'pObj' => &$this,
					'code' => $code,
				);
				if (($html = t3lib_div::callUserFunction($userFunc, $params, $this))) {
					return $html;
				}
			}
		}
		return '';
	}

	/**
	 * Checks if this URL is "no_cache" URL
	 *
	 * @param	string	$url	URL
	 * @return	boolean	true if URL is "no_cache" URL
	 */
	function isNoCacheUrl($url) {
		$parts = parse_url($url);
		// Brute force
		if (preg_match('/(^|&)no_cache=1/', $parts['query'])) {
			return true;
		}
		// Ideally we should have checked for alternative methods but they require TSFE
		// to be passed and therefore corrupted. So we do not do it now until we discover
		// how to make it without corrupting TSFE.
		return false;
	}

	/**
	 * Checks if valid item url is present.
	 * Valid item url is not empty, starts with http:// or https:// and
	 * its checksum match with passed checksum value
	 *
	 * @return	boolean	true if item url is valid
	 */
	function hasValidItemUrl() {
		$this->piVars['itemurl'] = trim($this->piVars['itemurl']);
		if (!$this->piVars['itemurl']) {
			return false;
		}
		if (!preg_match('/^https?:\/\//', $this->piVars['itemurl'])) {
			return false;
		}
		if ($this->piVars['itemurlchk'] != md5($this->piVars['itemurl'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
			return false;
		}
		return true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_pi1.php']);
}

?>