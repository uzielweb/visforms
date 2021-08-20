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
use Joomla\String\StringHelper;

class VisformsVisfieldRestrictUsedInCal extends VisformsVisfieldRestrict {

	public function __construct($value, $id, $name, $fid = null) {
		$this->type = 'usedInCal';
		parent::__construct($value, $id, $name, $fid);
	}

	protected function addRestricts() {
		if (($this->value !== '') && (!empty($this->fid))) {
			$pattern = '/\[[A-Z0-9]{1}[A-Z0-9\-]*]/';
			if (preg_match_all($pattern, $this->value, $matches)) {
				$db = JFactory::getDbo();
				$names = array();
				foreach ($matches[0] as $match) {
					$str = trim($match, '\[]');
					$names[] = $db->q(StringHelper::strtolower($str));
				}
				$names = implode(",", $names);

				$query = $db->getQuery(true);
				$query->select($db->qn('id'))
					->from($db->qn('#__visfields'))
					->where($db->qn('name') . " in (" . $names . ")")
					->where($db->qn('fid') . " = " . $this->fid);
				try {
					$db->setQuery($query);
					$fields = $db->loadColumn();
				}
				catch (runtimeExeption $e) {
					$test = true;
				}
			}
			if (!empty($fields)) {
				foreach ($fields as $field) {
					$restrict = array();
					$restrict['type'] = $this->type;
					$restrict['restrictedId'] = $field;
					$restrict['restrictorId'] = $this->id;
					$restrict['restrictorName'] = $this->name;
					$this->restricts[] = $restrict;
				}
			}
		}
	}
}