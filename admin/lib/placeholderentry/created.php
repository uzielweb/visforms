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

class VisformsPlaceholderEntryCreated extends VisformsPlaceholderEntry {

	public function __construct($param, $rawData, $field) {
		// call with $rawData = null, in order to get current date and time
		if (is_null($rawData)) {
			$rawData = VisformsHelper::getFormattedServerDateTime('now', JText::_('DATE_FORMAT_LC6'));
		}
		else {
			$rawData = VisformsHelper::getFormattedServerDateTime($rawData, JText::_('DATE_FORMAT_LC6'));
		}
		parent::__construct($param, $rawData, $field);
	}

	public function getReplaceValue() {
		return $this->rawData;
	}

}