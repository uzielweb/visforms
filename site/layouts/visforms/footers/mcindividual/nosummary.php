<?php
/**
 * Visforms html for form footer without summary page
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
if (!empty($displayData)) :
	if (isset($displayData['form']) && isset($displayData['nbFields']) && isset($displayData['hasRequired'])) :
		$form = $displayData['form'];
		$nbFields = $displayData['nbFields'];
		$hasRequired = $displayData['hasRequired'];
		$backButtonText = (!empty($form->backbtntext)) ? $form->backbtntext : JText::_('COM_VISFORMS_STEP_BACK');
		if (!empty($form->mpdisplaytype) && !empty($form->accordioncounter)) {
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		//Explantion for * if at least one field is requiered above captcha
		if ($hasRequired == true && $form->required == 'captcha') {
			echo JLayoutHelper::render('visforms.requiredtext.btdefault', array('form' => $form), null, array('component' => 'com_visforms'));
		}
		if (isset($form->captcha) && ($form->captcha == 1 || $form->captcha == 2)) {
			if ($form->formlayout == 'bt3mcindividual') {
				echo JLayoutHelper::render('visforms.captcha.bt3mcindividual', array('form' => $form), null, array('component' => 'com_visforms'));
			}
			else {
				echo JLayoutHelper::render('visforms.captcha.mcindividual', array('form' => $form), null, array('component' => 'com_visforms'));
			}
		}
		//Explantion for * if at least one field is requiered above submit
		if ($hasRequired == true && $form->required == 'bottom') {
			echo JLayoutHelper::render('visforms.requiredtext.btdefault', array('form' => $form), null, array('component' => 'com_visforms'));
		}
		echo '<div class="clearfix"></div>';
		for ($i = 0; $i < $nbFields; $i++) {
			$field = $form->fields[$i];
			if (!empty($field->sig_in_footer)) {
				echo $field->controlHtml;
				echo '<div class="clearfix"></div>';
			}
		}
		if (empty($form->hasBt3Layout)) {
			echo '<div class="form-actions">';
		}
		if (!empty($form->steps) && (int) $form->steps > 1) {
			echo ' <input type="button" class="btn back_btn" value="' . $backButtonText . '" /> ';
		}
		for ($i = 0; $i < $nbFields; $i++) {
			$field = $form->fields[$i];
			if (isset($field->isButton) && $field->isButton === true) {
				echo $field->controlHtml;
			}
		}
		if (empty($form->hasBt3Layout)) {
			echo '</div>';
		}
	endif;
endif; ?>