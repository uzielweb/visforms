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
require_once __DIR__ . '/uikit2base.php';

class VisformsUikit2FieldHelper extends VisformsUikit2BaseHelper {

	protected $field;

	public function setField($field) {
		$this->field = $field;
	}

	public function getCtrlGroupUikit2Classes() {
		$field = $this->field;
		$classes = (($field->labelBootstrapWidth != "10") ? ' uk-width'. $this->getWidth($field->labelBootstrapWidth) : ' uk-width-1-1');
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'labelBootstrapWidth' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			$classes .= ($field->$name != "10") ? ' uk-width-'. $lcBreakPoint  . $this->getWidth($field->$name) : '';
		}
		return $classes;
	}

	private function getLcBreakpoint($breakPoint) {
		switch ($breakPoint) {
			case 'Sm' :
				return 'small';
			case 'Md' :
				return 'medium';
			case 'Lg' :
				return 'large';
		}
	}

	public function getLabelClass() {
		$field = $this->field;
		$labelClass = 'uk-width' . $this->getLabelWidth($field->labelBootstrapWidth);
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'labelBootstrapWidth' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			// only add a label class for breakpoint if it is set
			$labelClass .= ($field->$name != "10") ? ' uk-width-' . $lcBreakPoint  . $this->getLabelWidth($field->$name) : '';
		}
		$labelClass .= (!empty($field->show_label)) ? ' uk-form-label' : ' uk-form-label';
		return $labelClass;
	}
}