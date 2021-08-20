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

class VisformsVisfieldRestrictUsedAsShowWhen extends VisformsVisfieldRestrict {

	public function __construct($value, $id, $name, $fid = null) {
		$this->type = 'usedAsShowWhen';
		parent::__construct($value, $id, $name, $fid);
	}

	protected function addRestricts() {
		if (is_array($this->value)) {
			foreach ($this->value as $value) {
				if (preg_match('/^field/', $value) === 1) {
					$restrict = array();
					$restrict['type'] = $this->type;
					$parts = explode('__', $value, 2);
					$restrict['restrictedId'] = parent::getRestrictedId($parts[0]);
					$restrict['restrictorId'] = $this->id;
					$restrict['restrictorName'] = $this->name;
					$restrict['optionId'] = $parts[1];
					$this->restricts[] = $restrict;
				}
			}
		}
	}
}