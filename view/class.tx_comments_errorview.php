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
 * This class implements a error view for the comments extension
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
class tx_comments_errorview extends tx_comments_baseview {

	/**
	 * Erros to display
	 *
	 * @var	array
	 */
	protected $errors;

	/**
	 * Creates an instance of this class. This view requires FE controller
	 * and will not work with AJAX controller.
	 *
	 * @param	tx_comments_fecontroller	$controller	Controller for this view
	 * @return	void
	 */
	public function __construct(tx_comments_fecontroller &$controller, array $errors) {
		parent::__construct($controller);
		$this->errors = $errors;
	}

	/**
	 * Renders the content of the view
	 *
	 * @return	string	HTML
	 */
	public function render() {
		// Get content object
		$cObj = &$this->controller->getCObj();

		// Get subparts
		$subpart = $cObj->getSubpart($this->templateCode, '###ERROR_SUB###');
		$errorSubpart = $cObj->getSubpart($subpart, '###ERROR###');

		// Create error list
		$content = '';
		foreach ($this->errors as $error) {
			$content .= $cObj->substituteMarker($errorSubpart, '###MESSAGE###',
				htmlspecialchars($error));
		}

		// Wrap into main subpart
		$content = $cObj->substituteSubpart($subpart, '###ERROR###', $content);

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_errorview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_errorview.php']);
}

?>