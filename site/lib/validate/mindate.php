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

class VisformsValidateMindate extends VisformsValidate
{
	protected $date;
	protected $minDate;
	protected $format;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		$this->date = $this->getTimesstamp($args['date'], $args['format']);
		$this->minDate = $this->getTimesstamp($args['mindate'], $args['mindateformat']);
	}

	protected function test() {
		if ($this->date < $this->minDate) {
			return false;
		} 
		else {
			return true;
		}
	}
	protected function getTimesstamp($date, $format) {
		$unifiedFromattedDate = DateTime::createFromFormat($format, $date);
		$unifiedFromattedDate->setTimezone(new DateTimeZone("UTC"));
		$unifiedFromattedDate->setTime(0,0);
		return($unifiedFromattedDate->getTimestamp());
	}
}