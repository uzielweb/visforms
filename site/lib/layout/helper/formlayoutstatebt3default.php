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

class FormLayoutStateBt3default implements FormLayoutState {
	public function fixInvalidLayoutSelection($formLayout) {
		$hasBt3Layouts = VisformsAEF::checkAEF(VisformsAEF::$bootStrap3Layouts);
		$form = $formLayout->getForm();
		if (empty($hasBt3Layouts)) {
			$form->formlayout = 'btdefault';
			$formLayout->updateForm($form);
			$formLayout->setFormLayoutState(new FormLayoutStateBtdefault());
		}
		else {
			if (isset($form->displaysublayout)) {
				switch ($form->displaysublayout) {
					case "horizontal" :
						$form->formlayout = "bt3horizontal";
						break;
					case "individual" :
						$form->formlayout = "bt3mcindividual";
						break;
				}
			}
			// displaysublayout not used. Set to default
			$form->displaysublayout = 'horizontal';
			$formLayout->updateForm($form);
		}
		return $form;
	}

	public function setLayoutOptions($formLayout) {
		// nothing to do
		return $formLayout->getForm();
	}
}