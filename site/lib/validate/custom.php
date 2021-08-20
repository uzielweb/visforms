<?php
/**
 * Visforms validate custom regex class
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsValidateCustom extends VisformsValidate
{

	protected $value;
	protected $regex;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		//we expect an item with key 'value' and an item with key 'regex' in $args
		$this->value = isset($args['value']) ? $args['value'] : "";
		$this->regex = isset($args['regex']) ? $args['regex'] : "";
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