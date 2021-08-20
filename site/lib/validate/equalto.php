<?php
/**
 * Visforms validate equalTo class
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

class VisformsValidateEqualto extends VisformsValidate
{
	protected $value;
	protected $cvalue;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		//we expect an item with key 'value' and an item with key 'cvalue' in $args
		$this->value = isset($args['value']) ? $args['value'] : '';
		$this->cvalue = isset($args['cvalue']) ? $args['cvalue'] : '';
	}

	protected function test() {
		if ($this->value === $this->cvalue) {
			return true;

		} 
		else {
			return false;
		}
	}
}