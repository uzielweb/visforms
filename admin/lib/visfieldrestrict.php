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

abstract class VisformsVisfieldRestrict {

	protected $value;
	protected $id;
	protected $name;
	protected $fid;
	protected $restricts = array();

	public function __construct($value, $id, $name, $fid = null) {

		$this->value = $value;
		$this->id = $id;
		$this->name = $name;
		$this->fid = $fid;
		$this->addRestricts();
	}

	public function getRestricts() {
		return $this->restricts;
	}

	public static function getRestrictedId($restrict) {
		// get numeric part = fieldId
		return preg_replace('/[^0-9]/', '', $restrict);
	}

	abstract protected function addRestricts();
}