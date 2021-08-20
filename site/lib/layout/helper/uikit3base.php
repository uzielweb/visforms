<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2019 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class VisformsUikit3BaseHelper {

	protected $breakPoints;

	public function __construct() {
		$this->breakPoints = array('Sm', 'Md', 'Lg', 'Xl');
	}

	protected function getWidth($input) {
		switch ($input) {
			case 1 :
				return '-5-6';
				break;
			case 2:
				return '-2-3';
				break;
			case 3:
				return '-1-2';
				break;
			case 4:
				return '-1-3';
				break;
			case 5 :
				return '-1-6';
				break;
			default:
				return '-1-1';
				break;
		}
	}

	protected function getLabelWidth($input) {
		switch ($input) {
			case 1 :
				return '-1-6';
				break;
			case 2:
				return '-1-3';
				break;
			case 3:
				return '-1-2';
				break;
			case 4:
				return '-2-3';
				break;
			case 5 :
				return '-5-6';
				break;
			default:
				return '-1-1';
				break;
		}
	}
}