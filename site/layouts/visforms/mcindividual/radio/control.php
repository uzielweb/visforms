<?php
/**
 * Visforms control html for radio for multi column layout
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
		$inputAttributes = (!empty($field->attributeArray)) ? ArrayHelper::toString($field->attributeArray, '=', ' ', true) : '';
		$asList = (isset($field->display) && $field->display == 'LST') ? true : false;
		for ($j = 0; $j < $k; $j++) {
			$labelClass = (!empty($field->opts[$j]['labelclass'])) ? $field->opts[$j]['labelclass'] . ' ' : '';
			if ($asList) {
				$labelClass .= "radio";
			}
			else {
				$labelClass .= "radio inline";
			}
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
			$html[] = '<label style="margin-bottom: 9px;" class="' . $labelClass . '" id="' . $field->name . 'lbl_' . $j . '" for="field' . $field->id . '_' . $j . '">' . $field->opts[$j]['label'];
			$html[] = '<input id="field' . $field->id . '_' . $j . '" name="' . $field->name . '" value="' . $field->opts[$j]['value'] . '" ' . $checked . $disabled . $inputAttributes . ' aria-labelledby="' . $field->name . 'lbl ' . $field->name . 'lbl_' . $j . '" data-error-container-id="fc-tbxfield' . $field->id . '" />';
			$html[] = '</label>';
		}
		echo implode('', $html);
	endif;
endif; ?>