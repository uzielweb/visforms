<?php
/**
 * Visforms validate number class
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

class VisformsValidateNumber extends VisformsValidate
{
	protected $value;
	protected $regex;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		$this->regex = '/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/';
		$this->value = isset($args['value']) ? $args['value'] : 0;
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