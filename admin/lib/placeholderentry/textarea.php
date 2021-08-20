<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsPlaceholderEntryTextarea extends VisformsPlaceholderEntry {

	public function getReplaceValue() {
		if (!empty($this->field->keepBr)) {
			return JHtmlVisforms::replaceLinebreaks($this->rawData, "<br />");
		}
		else {
			return JHtmlVisforms::replaceLinebreaks($this->rawData, " ");
		}
	}

}