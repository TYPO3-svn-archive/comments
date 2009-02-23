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

require_once(t3lib_extMgm::extPath('comments', 'controller/class.tx_comments_basecontroller.php'));

/**
 * This class implements a base class for all comment extension views.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_comments
 */
abstract class tx_comments_baseview {

	/**
	 * Controller instance for this class
	 *
	 * @var	tx_comments_basecontroller
	 */
	protected $controller;

	/**
	 * Fetches template file content for the configured template
	 *
	 * @var	string
	 */
	protected $templateCode;

	/**
	 * Content object
	 *
	 * @var	tslib_cObj
	 */
	protected $cObj;

	/**
	 * Plugin configuration
	 *
	 * @var	array
	 */
	protected $conf;

	/**
	 * Creates an instance of this class
	 *
	 * @return	void
	 */
	public function __construct(tx_comments_basecontroller &$controller) {
		$this->controller = $controller;
		$this->cObj = &$this->controller->getCObj();
		$this->conf = $this->controller->getConfiguration();
		$this->fetchTemplateCode();
	}

	/**
	 * Renders the content of the view
	 *
	 * @return	string	HTML
	 */
	abstract public function render();

	/**
	 * Obtains template file content for this plugin
	 *
	 * @return	void
	 */
	protected function fetchTemplateCode() {
		$fileName = $this->conf['templateFile'];
		$this->templateCode = $this->controller->getCObj()->fileResource($fileName);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_baseview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/view/class.tx_comments_baseview.php']);
}

?>