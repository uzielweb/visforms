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

class VisformsUikit2FormHelper extends VisformsUikit2BaseHelper {

	protected $form;

	public function setForm($form) {
		$this->form = $form;
	}

	public function getCtrlGroupUikit2Classes() {
		$form = $this->form;
		$classes = (($form->captchaLabelUikit2Width != "10") ? ' uk-width'. $this->getWidth($form->captchaLabelUikit2Width) : ' uk-width-1-1');
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'captchaLabelUikit2Width' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			$classes .= ($form->$name != "10") ? ' uk-width-' . $lcBreakPoint . $this->getWidth($form->$name) : '';
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
		$form = $this->form;
		$labelClass = 'uk-width' . $this->getLabelWidth($form->captchaLabelUikit2Width);
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'captchaLabelUikit2Width' . $breakPoint;
			$lcBreakPoint = $this->getLcBreakpoint($breakPoint);
			// only add a label class for breakpoint if it is set
			$labelClass .= ($form->$name != "10") ? ' uk-width-' . $lcBreakPoint . $this->getLabelWidth($form->$name) : '';
		}
		$labelClass .= (!empty($form->show_label)) ? ' uk-form-label uk-hidden' : ' uk-form-label';
		return $labelClass;
	}
}