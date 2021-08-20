<?php
/**
 * Visforms control html for checkbox group for default layout
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');
if (!empty($displayData)) :
	if (isset($displayData['field'])) :
		$field = $displayData['field'];
		$html = array();
		$k = count($field->opts);
		$checked = "";
		$asList = (isset($field->display) && $field->display == 'LST') ? true : false;
		if ($asList) {
			//Show radios as a list; Wrap them in an div
			$html[] = '<div class="visCSSclear ' . $field->fieldCSSclass . '">';
		}
		else {
			$html[] = '<p class="visCSStop0 visCSSmargLeft visCSSrbinl ' . $field->fieldCSSclass . '">';
		}
		for ($j = 0; $j < $k; $j++) {
			// option specific label class
			$labelClass = (!empty($field->opts[$j]['labelclass'])) ? $field->opts[$j]['labelclass'] . ' ' : '';
			$inputAttributes = (!empty($field->attributeArray)) ? ArrayHelper::toString($field->attributeArray, '=', ' ', true) : '';
			if ($field->opts[$j]['selected'] != false) {
				$checked = 'checked="checked" ';
			}
			else {
				$checked = "";
			}
			if (!empty($field->opts[$j]['disabled'])) {
				$disabled = ' disabled="disabled" data-disabled="disabled" ';
			}
			else {
				$disabled = "";
			}
			if ($asList) {
				if ($j != 0) {
					$html[] = '<br />';
				}
				$labelClass .= 'visCSSbot5 visCSSrllst ';
				$html[] = '<label class="' . $labelClass . ' ' . $field->labelCSSclass . '" id="' . $field->name . 'lbl_' . $j . '" for="field' . $field->id . '_' . $j . '">' . $field->opts[$j]['label'] . '</label>';
				$html[] = '<input id="field' . $field->id . '_' . $j . '" name="' . $field->name . '[]" value="' . $field->opts[$j]['value'] . '" ' . $checked . $disabled . $inputAttributes . '" data-error-container-id="fc-tbxfield' . $field->id . '" />';
			}
			else {
				$labelClass .= ' visCSStop10 visCSSright20 visCSSrlinl ';
				$html[] = '<input id="field' . $field->id . '_' . $j . '" name="' . $field->name . '[]" value="' . $field->opts[$j]['value'] . '" ' . $checked . $disabled . $inputAttributes . '" data-error-container-id="fc-tbxfield' . $field->id . '" />';
				$html[] = '<label class="' . $labelClass . ' ' . $field->labelCSSclass . '" id="' . $field->name . 'lbl_' . $j . '" for="field' . $field->id . '_' . $j . '">' . $field->opts[$j]['label'] . '</label>';
			}
		}
		if ($asList) {
			$html[] = '</div>';
		}
		else {
			$html[] = '</p>';
		}
		echo implode('', $html);
	endif;
endif; ?>