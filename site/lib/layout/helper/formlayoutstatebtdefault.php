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

class FormLayoutStateBtdefault implements FormLayoutState {
	public function fixInvalidLayoutSelection($formLayout) {
		$form = $formLayout->getForm();
		if (isset($form->displaysublayout)) {
			switch ($form->displaysublayout) {
				case "horizontal" :
					$form->formlayout = "bthorizontal";
					break;
				case "individual" :
					$form->formlayout = "mcindividual";
					break;
			}
		}
		$formLayout->updateForm($form);
		return $form;
	}

	public function setLayoutOptions($formLayout) {
		// nothing to do
		return $formLayout->getForm();
	}
}