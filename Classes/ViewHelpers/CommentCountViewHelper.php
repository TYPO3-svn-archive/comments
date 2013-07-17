<?php
namespace Tx\Comments\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Ingo Renner (ingo@typo3.org)
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * View helper to count comments for a particular record
 *
 * # Example: Basic example for use with EXT:news
 * # Description: Simply add the comments namespace and insert the view helper in the List/Item partial, then pass it the whole news item.
 * <code>
 * {namespace c=Tx\Comments\ViewHelpers}
 * <c:commentCount item="{newsItem}" />
 * </code>
 * <output>
 *  number of comments the current newsItem received so far
 * </output>
 *
 * @package TYPO3
 * @subpackage tx_comments
 */
class CommentCountViewHelper extends AbstractViewHelper {

	/**
	 * Renders the number of comments a news record received
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $item
	 * @return string Comment count
	 */
	public function render(AbstractDomainObject $item) {

		// get model table name
		$modelClassName = get_class($item);
		$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$namingUtility = $objectManager->get('Tx\\Comments\\NamingUtility');
		$tableName = $namingUtility->translateModelClassNameToTableName($modelClassName);

		$uid = $item->getUid();

		$commentCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'uid',
			'tx_comments_comments',
			'external_ref = \'' . $tableName . '_' . $uid . '\' '
				. $GLOBALS['TSFE']->sys_page->enableFields('tx_comments_comments')
		);

		return $commentCount;
	}

}

?>