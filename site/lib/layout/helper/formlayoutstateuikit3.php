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
require_once JPATH_ROOT . '/components/com_visforms/lib/layout/helper/uikit3form.php';

class FormLayoutStateUikit3 implements FormLayoutState {

	protected $breakPoints = array('Sm', 'Md', 'Lg', 'Xl');
	protected $form;
	protected $formHelper;

	public function fixInvalidLayoutSelection($formLayout) {
		$hasUikit3Layouts = VisformsAEF::checkAEF(VisformsAEF::$uikit3Layouts);
		$form = $formLayout->getForm();
		if (empty($hasUikit3Layouts)) {
			$form->formlayout = 'btdefault';
			$form->displaysublayout = 'horizontal';
			$formLayout->updateForm($form);
			$formLayout->setFormLayoutState(new FormLayoutStateBtdefault());
		}
		return $form;
	}

	public function setLayoutOptions($formLayout) {
		$this->form = $formLayout->getForm();
		if (empty($this->form->captchaLabelUikit3Width) || ($this->form->displaysublayout != 'individual')) {
			if ($this->form->displaysublayout == 'stacked' || $this->form->displaysublayout == 'individual') {
				$this->form->captchaLabelUikit3Width = "6";
			}
			else {
				$this->form->captchaLabelUikit3Width = "2";
			}
		}
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'captchaLabelUikit3Width' . $breakPoint;
			if (empty($this->form->$name) || ($this->form->displaysublayout != 'individual')) {
				if ($this->form->displaysublayout == 'stacked' || $this->form->displaysublayout == 'individual') {
					$this->form->$name = "6";
				}
				else {
					$this->form->$name = "2";
				}
			}
		}
		$this->formHelper = new VisformsUikit3FormHelper();
		$this->formHelper->setForm($this->form);
		$this->getCtrlGroupClasses();
		$this->getCaptchaLabelClasses();
		$this->setButtonClass();
		$formLayout->updateForm($this->form);
		return $this->form;
	}

	protected function setButtonClass() {
		if (empty($this->form->summarybtncssclass)) {
			$this->form->summarybtncssclass = 'uk-button-default';
		}
		if (empty($this->form->correctbtncssclass)) {
			$this->form->correctbtncssclass = 'uk-button-default';
		}
		if (empty($this->form->backbtncssclass)) {
			$this->form->backbtncssclass = 'uk-button-default';
		}
		if (empty($this->form->savebtncssclass)) {
			$this->form->savebtncssclass = 'uk-button-primary';
		}
		if (empty($this->form->cancelbtncssclass)) {
			$this->form->cancelbtncssclass = 'uk-button-secondary';
		}
	}

	protected function getCtrlGroupClasses() {
		$this->form->ctrlGroupBtClasses =  $this->formHelper->getCtrlGroupUikit3Classes();
	}


	protected function getCaptchaLabelClasses() {
		$this->form->captchaLabelClasses = $this->formHelper->getLabelClass();
	}
}