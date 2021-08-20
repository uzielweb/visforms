<?php
/**
 * Visforms validate max class
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

class VisformsValidateMax extends VisformsValidate
{
	protected $count;
	protected $maxCount;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		//we expect an item with key 'count' and an item with key 'maxcount' in $args
		$this->count = isset($args['count']) ? $args['count'] : 0;
		$this->maxCount = isset($args['maxcount']) ? $args['maxcount'] : 0;
	}

	protected function test() {
		if ($this->count > $this->maxCount) {
			return false;

		} 
		else {
			return true;
		}
	}
}