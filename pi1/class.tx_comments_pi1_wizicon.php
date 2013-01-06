<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov <dmitry@typo3.org>
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
 *
 *
 *   45: class tx_comments_pi1_wizicon
 *   53:     function proc($wizardItems)
 *   73:     function includeLocalLang()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Class that adds the wizard icon.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
class tx_comments_pi1_wizicon {

	/**
	 * Processing the wizard items array
	 *
	 * @param	array		$wizardItems: The wizard items
	 * @return	array		Modified array with wizard items
	 */
	function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_comments_pi1'] = array(
			'icon'=>t3lib_extMgm::extRelPath('comments').'pi1/ce_wiz.gif',
			'title'=>$LANG->getLLL('tt_content.list_type_pi1', $LL),
			'description'=>$LANG->getLLL('pi1_plus_wiz_description',$LL),
			'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=comments_pi1'
		);

		return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return	array		The array with language labels
	 */
	function includeLocalLang()	{
		switch (TYPO3_version) {
			case '4.5':
				$llFile     = t3lib_extMgm::extPath('comments').'locallang.xml';
				$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
				break;
			case '4.6':
			case '4.7':
				$llFile     = t3lib_extMgm::extPath('comments').'locallang.xml';
				$LOCAL_LANG = t3lib_l10n_parser_Llxml::getParsedData($llFile, $GLOBALS['LANG']->lang);
				break;
			case '6.0':
			default:
				$llFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('comments').'pi1/locallang.xml';

				$localLanguageParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Localization\\Parser\\LocallangXmlParser');
				$LOCAL_LANG = $localLanguageParser->getParsedData($llFile, $GLOBALS['LANG']->lang);
		}

		return $LOCAL_LANG;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_pi1_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_pi1_wizicon.php']);
}
?>