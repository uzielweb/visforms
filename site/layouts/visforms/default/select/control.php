<?php
/**
 * Visforms control html for select for default layout
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

use Joomla\Utilities\ArrayHelper;

if (!empty($displayData)) :
	if (isset($displayData['field'])) :
		$field = $displayData['field'];
		$k = count($field->opts);
		$html = array();
		if (!empty($field->render_as_datalist)) {
			if ((!empty($field->attributeArray)) && isset($field->attributeArray['class'])) {
				// a bit dirty, but we have to remove a potential "form-control" in class attribute
				$field->attributeArray['class'] = str_replace('form-control', '', $field->attributeArray['class']);
			}
			$attribs =  (!empty($field->attributeArray)) ? ArrayHelper::toString($field->attributeArray, '=',' ', true) : '';
			$html[] = '<table id="field'.$field->id.'" '.$attribs.'>';
			for ($j = 0; $j < $k; $j++) {
				$html[] = '<tr>';
				$html[] = '<td>';
				$html[] = $field->opts[$j]['label'];
				$html[] = '</td>';
				$html[] = '</tr>';
			}
			$html[] = '</table>';
		}
		else {
			$options = array();
			$checked = array();
			$hasSelectedItem = false;
			//Has select no default value? Then we need a supplementary 'default' option for selects that are not "multiple" or have a height < 1. Otherwise the first option can not be selected properly.
			for ($j = 0; $j < $k; $j++) {
				if ($field->opts[$j]['selected'] != false) {
					$hasSelectedItem = true;
					break;
				}
			}
			if (((empty($field->attribute_multiple))
				&& (empty($field->attribute_size))
				&& (empty($hasSelectedItem)))) {
				$options[] = JHtml::_('select.option', '', ((empty($field->customselectvaluetext)) ? JText::_('CHOOSE_A_VALUE') : $field->customselectvaluetext));
			}
			for ($j = 0; $j < $k; $j++) {
				$optKey = array();
				if ($field->opts[$j]['selected'] != false) {
					$checked[] = $field->opts[$j]['value'];
				}
				if (!empty($field->opts[$j]['disabled'])) {
					$optKey['disable'] = true;
				}
				$options[] = JHtml::_('select.option', $field->opts[$j]['value'], $field->opts[$j]['label'], $optKey);
			}
			$html[] = JHtml::_('select.genericlist', $options, $field->name . '[]', array('id' => 'field' . $field->id, 'list.attr' => $field->attributeArray, 'list.select' => $checked));
		}
		echo implode('', $html);
	endif;
endif; ?>