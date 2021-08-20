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

class VisformsVisfieldRestrictUsedAsMaxDate extends VisformsVisfieldRestrict {

	public function __construct($value, $id, $name, $fid = null) {
		$this->type = 'usedAsMaxDate';
		parent::__construct($value, $id, $name, $fid);
	}

	protected function addRestricts() {
		if ((strpos($this->value, '#field') === 0 )) {
			$restrict = array();
			$restrict['type'] = $this->type;
			$restrict['restrictedId'] = parent::getRestrictedId($this->value);
			$restrict['restrictorId'] = $this->id;
			$restrict['restrictorName'] = $this->name;
			$this->restricts[] = $restrict;
		}
	}
}