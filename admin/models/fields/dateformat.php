<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/models/visdatas.php');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class JFormFieldDateFormat extends JFormFieldList
{
	public $type = 'DateFormat';

	protected function getInput()
	{
		$html = array();
		$attr = '';
		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
        $form = $this->form; 
        $label = $form->getFieldAttribute($this->fieldname, 'label');

        //get field defaultvalues
        $model = new VisformsModelVisdatas();
        $datas = $model->getItems();
        $id = JFactory::getApplication()->input->getInt('id', 0);
        //as soon as user inputs are stored we do not allow to change date format
        if ((isset($datas)) && is_array($datas) && (count($datas) > 0)) {
            $fname = 'F'.$id;
            foreach ($datas as $data) {
                if (isset($data->$fname) && ($data->$fname != '')) {
                    $attr .= ' onchange="formatFieldDateChange(this, \'' . $this->value. '\', \'' . JText::sprintf(("COM_VISFORMS_DATEFORMAT_CANNOT_BE_CHANGED_JS")) . '\')"';
                    break;
                }
            }
	        $attr .= ' onchange="formatFieldDateChange(this, \'' . $this->value. '\', \'\')"';
        } else {
	        $attr .= ' onchange="formatFieldDateChange(this, \'' . $this->value. '\', \'\')"';
        }

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		} else {
			// Create a regular list.
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$options[0]       = JHtml::_('select.option',  'd.m.Y;%d.%m.%Y', 'DD.MM.YYYY');
		$options[1]         = JHtml::_('select.option',  'm/d/Y;%m/%d/%Y','MM/DD/YYYY');
		$options[2]         = JHtml::_('select.option',  'Y-m-d;%Y-%m-%d','YYYY-MM-DD');

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
