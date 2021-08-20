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

class VisformsPlaceholderEntryLocation extends VisformsPlaceholderEntry {

	protected static $customParams = array (
		'LAT' => 'COM_VISFORMS_PLACEHOLDER_PARAM_LAT_ONLY',
		'LNG' => 'COM_VISFORMS_PLACEHOLDER_PARAM_LNG_ONLY'
	);

	public function getReplaceValue() {
		$customParams = self::$customParams;
		if (!empty($this->param) && array_key_exists($this->param, $customParams) && !empty($this->rawData)) {
			$values = VisformsHelper::registryArrayFromString($this->rawData);
			switch ($this->param) {
				case 'LAT' :
					return $values['lat'];
				case 'LNG' :
					return $values['lng'];
				default:
					return '';
			}
		}
		return $this->rawData;
	}
}