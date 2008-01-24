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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hooks to tt_news.
 *
 * $Id: class.tx_comments_tcemain.php 7093 2007-10-24 12:39:55Z liels_bugs $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

/**
 * This clas provides hook to tt_news to add extra markers.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage comments
 */
class tx_comments_ttnews {
	/**
	 * Processes comments-specific markers for tt_news
	 *
	 * @param	array	$markerArray	Array with merkers
	 * @param	array	$row	tt_news record
	 * @param	array	$lConf	Configuration array for current tt_news view
	 * @param	tx_ttnews	$pObj	Reference to parent object
	 * @return	array	Modified marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		return $markerArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_ttnews.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_ttnews.php']);
}

?>