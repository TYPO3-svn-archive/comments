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

require_once(t3lib_extMgm::extPath('comments', 'view/class.tx_comments_baseview.php'));

/**
 * This class implements a form view for the comments extension. This class
 * only displays the form, it does not process form submissions. If you look
 * for submission code, look in ../controller/class.tx_comments_fecontroller.php
 * instead.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
class tx_comments_formview extends tx_comments_baseview {

	/**
	 * Renders form for the comments extension
	 *
	 * @return	string	Generated HTML
	 */
	public function render() {
		$content = '';

		$cObj = &$this->controller->getCObj();
		$lang = &$this->controller->getLang();

		// Get subpart
		$subpart = $cObj->getSubpart($this->templateCode, '###COMMENT_FORM###');

		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		$currentComment = $this->controller->getSubmittedComment();
		/* @var $currentComment tx_comments_comment */
		$validationErrors = $currentComment->getValidationResults();
		$markers = array(
			'###ACTION_URL###' => htmlspecialchars($url),
			'###CAPTCHA###' => $this->getCaptcha($cObj, $lang),
			'###CONTENT###' => count($validationErrors) > 0 ? htmlspecialchars($currentComment->getContent()) : '',
			'###CURRENT_URL###' => htmlspecialchars($url),
			'###CURRENT_URL_CHK###' => md5($url . $cObj->data['uid'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']),
			'###EMAIL###' => htmlspecialchars($currentComment->getEmail()),
			'###ERROR_CONTENT###' => (isset($validationErrors['content']) ? htmlspecialchars($validationErrors['content']) : ''),
			'###ERROR_EMAIL###' => (isset($validationErrors['email']) ? htmlspecialchars($validationErrors['email']) : ''),
			'###ERROR_FIRSTNAME###' => (isset($validationErrors['firstname']) ? htmlspecialchars($validationErrors['firstname']) : ''),
			'###ERROR_HOMEPAGE###' => (isset($validationErrors['homepage']) ? htmlspecialchars($validationErrors['homepage']) : ''),
			'###ERROR_LASTNAME###' => (isset($validationErrors['lastname']) ? htmlspecialchars($validationErrors['lastname']) : ''),
			'###ERROR_LOCATION###' => (isset($validationErrors['location']) ? htmlspecialchars($validationErrors['location']) : ''),
			'###FIRSTNAME###' => htmlspecialchars($currentComment->getFirstName()),
			'###JS_USER_DATA###' => '',
			'###HOMEPAGE###' => htmlspecialchars($currentComment->getHomePage()),
			'###LASTNAME###' => htmlspecialchars($currentComment->getLastName()),
			'###LOCATION###' => htmlspecialchars($currentComment->getLocation()),
			'###TEXT_ADD_COMMENT###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.add_comment'),
			'###TEXT_CONTENT###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.content'),
			'###TEXT_EMAIL###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.email'),
			'###TEXT_FIRST_NAME###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.first_name'),
			'###TEXT_LAST_NAME###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.last_name'),
			'###TEXT_LOCATION###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.location'),
			'###TEXT_REQUIRED_HINT###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.required_field'),
			'###TEXT_SUBMIT###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.submit'),
			'###TEXT_RESET###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.reset'),
			'###TEXT_WEB_SITE###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.web_site'),
			'###TOP_MESSAGE###' => '',
		);

		$content = $cObj->substituteMarkerArray($subpart, $markers);

		return $content;
	}

	/**
	 * Obtains captcha code according to the configuration
	 *
	 * @param	tslib_cObj	$cObj	Content object
	 * @return	string	Generated captcha HTML code
	 */
	protected function getCaptca(tslib_cObj &$cObj, language &$lang) {
		$result = '';
		$conf = $this->controller->getConfiguration();
		if ($conf['spamProtect.']['useCaptcha']) {
			$subpart = $cObj->getSubpart($this->templateCode, '###CAPTCHA_SUB###');
			if ($conf['spamProtect.']['useCaptcha'] == 1 && t3lib_extMgm::isLoaded('captcha')) {
				$result = $this->getCaptchaFromCaptchaExt($cObj, $lang, $conf, $subpart);
			}
			elseif ($conf['spamProtect.']['useCaptcha'] == 2 && t3lib_extMgm::isLoaded('sr_freecap')) {
				$result = $this->getCaptchaFromSrFreeCap($cObj, $lang, $conf, $subpart);
			}
		}
		return $result;
	}

	/**
	 * Obtains captcha from captcha extension
	 *
	 * @param	tslib_cObj	$cObj	Content object
	 * @param	language	$lang	Language object
	 * @param	array	$conf	Configuration
	 * @param	string	$subpart	Subpart
	 * @return	string	Generated HTML
	 */
	protected function getCaptchaFromCaptchaExt(tslib_cObj &$cObj, language &$lang, $conf, $subpart) {
		$code = $cObj->substituteMarkerArray($subpart, array(
						'###SR_FREECAP_IMAGE###' => '<img src="' . t3lib_extMgm::siteRelPath('captcha') . 'captcha/captcha.php" alt="" />',
						'###SR_FREECAP_CANT_READ###' => '',
						'###REQUIRED_CAPTCHA###' => $cObj->getSubpart($this->templateCode, '###REQUIRED_FIELD###'),
//						'###ERROR_CAPTCHA###' => $this->form_wrapError('captcha'),
						'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
						'###TEXT_ENTER_CODE###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.enter_code'),
					));
		return str_replace('<br /><br />', '<br />', $code);
	}

	/**
	 * Obtains captcha from captcha extension
	 *
	 * @param	tslib_cObj	$cObj	Content object
	 * @param	language	$lang	Language object
	 * @param	array	$conf	Configuration
	 * @param	string	$subpart	Subpart
	 * @return	string	Generated HTML
	 */
	protected function getCaptchaFromSrFreeCap(tslib_cObj &$cObj, language &$lang, $subpart) {
		require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
		$freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
		/* @var $freeCap tx_srfreecap_pi2 */
		return $this->cObj->substituteMarkerArray($subpart, array_merge($freeCap->makeCaptcha(), array(
						'###REQUIRED_CAPTCHA###' => $cObj->getSubpart($this->templateCode, '###REQUIRED_FIELD###'),
						'###ERROR_CAPTCHA###' => $this->wrapMissingFieldError('captcha'),
						'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
						'###TEXT_ENTER_CODE###' => $lang->sL('LLL:EXT:comments/pi1/locallang.xml:pi1_template.enter_code'),
					)));
	}

	/**
	 * Wraps error message with the stdWrap.
	 *
	 * @param	string	$text	Text
	 * @return	string	HTML
	 */
	protected function wrapMissingFieldError(tslib_cObj &$cObj, array $conf, $field) {
		// ?????
		return $cObj->stdWrap(htmlspecialchars($text), $conf['requiredFields_errorWrap.']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_formview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_formview.php']);
}

?>