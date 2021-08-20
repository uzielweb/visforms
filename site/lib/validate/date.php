<?php
/**
 * Visforms validate date class
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

class VisformsValidateDate extends VisformsValidate
{
	protected $value;
	protected $format;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		//we expect an item with key 'value' and an item with key 'format' in $args
		$this->value = isset($args['value']) ? $args['value'] : "";
		$this->format = isset($args['format']) ? $args['format'] : "";
	}

	protected function test() {
		$value = $this->value;
		$dformat = $this->format;

		//check for correct amount of characters (10)
		if (strlen($value) != 10) {
			return false;
		}

		if ($dformat == '') {
			//This should never happen! no format given, something went wrong with the field, we cannot validate
			//todo maybe throw an error?
			return false;
		}

		//get format of delimiter used in value
		$delimiterUsed = $this->getUsedDelimiter();
		if ($delimiterUsed == '') {
			//value does not contain any of the allowed delimiter characters
			return false;
		}

		//check that value uses the correct delimiter
		if (!(strpos($dformat, $delimiterUsed) > 0)) {
			return false;
		}

		//get date parts
		$date = explode($delimiterUsed, $value);
		if (count($date) !== 3) {
			//wrong amount of parts
			return false;
		}

		(int) $day = 0;
		(int) $month = 0;
		(int) $year = 0;

		switch ($delimiterUsed) {
			case  '.' :
				$day = (isset($date[0]) === true) ? (int) $date[0] : (int) 0;
				$month = (isset($date[1]) === true) ? (int) $date[1] : (int) 0;
				$year = (isset($date[2]) === true) ? (int) $date[2] : (int) 0;
				break;
			case  '/' :
				$month = (isset($date[0]) === true) ? (int) $date[0] : (int) 0;
				$day = (isset($date[1]) === true) ? (int) $date[1] : (int) 0;
				$year = (isset($date[2]) === true) ? (int) $date[2] : (int) 0;
				break;
			case  '-' :
				$year = (isset($date[0]) === true) ? (int) $date[0] : (int) 0;
				$month = (isset($date[1]) === true) ? (int) $date[1] : (int) 0;
				$day = (isset($date[2]) === true) ? (int) $date[2] : (int) 0;
				break;
			default :
				break;
		}

		return checkdate($month, $day, $year);
	}

	private function getUsedDelimiter() {
		$value = $this->value;
		switch ($value) {
			case (strpos($value, '.') > 0) :
				$delimiter = '.';
				break;
			case (strpos($value, '/') > 0) :
				$delimiter = '/';
				break;
			case (strpos($value, '-') > 0) :
				$delimiter = '-';
				break;
			default :
				$delimiter = '';
				break;
		}
		return $delimiter;
	}
}