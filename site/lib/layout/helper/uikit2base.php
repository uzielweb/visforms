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

abstract class VisformsUikit2BaseHelper {

	protected $breakPoints;

	public function __construct() {
		$this->breakPoints = array('Sm', 'Md', 'Lg');
	}

	protected function getWidth($input) {
		switch ($input) {
			case 1 :
				return '-9-10';
				break;
			case 2:
				return '-8-10';
				break;
			case 3:
				return '-7-10';
				break;
			case 4:
				return '-6-10';
				break;
			case 5 :
				return '-5-10';
				break;
			case 6 :
				return '-4-10';
				break;
			case 7 :
				return '-3-10';
				break;
			case 8 :
				return '-2-10';
				break;
			case 9 :
				return '-1-10';
				break;
			default:
				return '-10-10';
				break;
		}
	}

	protected function getLabelWidth($input) {
		switch ($input) {
			case 1 :
				return '-1-10';
				break;
			case 2:
				return '-2-10';
				break;
			case 3:
				return '-3-10';
				break;
			case 4:
				return '-4-10';
				break;
			case 5 :
				return '-5-10';
				break;
			case 6 :
				return '-6-10';
				break;
			case 7 :
				return '-7-10';
				break;
			case 8 :
				return '-8-10';
				break;
			case 9 :
				return '-9-10';
				break;
			default:
				return '-10-10';
				break;
		}
	}
}