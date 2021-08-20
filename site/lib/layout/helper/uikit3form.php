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
require_once __DIR__ . '/uikit3base.php';

class VisformsUikit3FormHelper extends VisformsUikit3BaseHelper {

	protected $form;

	public function setForm($form) {
		$this->form = $form;
	}

	public function getCtrlGroupUikit3Classes() {
		$form = $this->form;
		$classes = (($form->captchaLabelUikit3Width != "6") ? ' uk-width'. $this->getWidth($form->captchaLabelUikit3Width) : ' uk-width-1-1');
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'captchaLabelUikit3Width' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			$classes .= ($form->$name != "6") ? ' uk-width' . $this->getWidth($form->$name) . '@' . $lcBreakPoint : '';
		}
		return $classes;
	}

	private function getLcBreakpoint($breakPoint) {
		$lcBreakPoint = substr(lcfirst($breakPoint), 0,1);
		return ($lcBreakPoint == 'x') ? 'xl' : $lcBreakPoint;
	}

	public function getLabelClass() {
		$form = $this->form;
		$labelClass = 'uk-width' . $this->getLabelWidth($form->captchaLabelUikit3Width);
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'captchaLabelUikit3Width' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			// only add a label class for breakpoint if it is set
			$labelClass .= ($form->$name != "6") ? ' uk-width' . $this->getLabelWidth($form->$name) . '@' . $lcBreakPoint : '';
		}
		$labelClass .= (!empty($form->show_label)) ? ' uk-form-label uk-hidden' : ' uk-form-label';
		return $labelClass;
	}
}