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

class VisformsPlaceholderEntryIpaddress extends VisformsPlaceholderEntry {

	public function __construct($param, $rawData, $field) {
		// call with $rawData = null, in order to get current user IP from $_SERVER
		if (is_null($rawData)) {
			$rawData = $_SERVER['REMOTE_ADDR'];
		}
		parent::__construct($param, $rawData, $field);
	}

	public function getReplaceValue() {
		return $this->rawData;
	}

}