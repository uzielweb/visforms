<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsValidateLongitude extends VisformsValidate
{
	protected $value;
	protected $regex;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		//we expect an item with key 'value' and an item with key 'regex' in $args
		$this->value = isset($args['value']) ? $args['value'] : "";
		$this->regex = "/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/";
	}

	protected function test() {
		if (!(preg_match($this->regex, $this->value) == true)) {
			return false;

		} 
		else {
			return true;
		}
	}
}