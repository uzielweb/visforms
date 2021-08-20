<?php
/**
 * Visforms field check business class
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

class VisformsBusinessCheckbox extends VisformsBusiness
{
	public function getFields() {
		$this->setField();
		return $this->fields;
	}

	protected function setField() {
		$this->setIsDisabled();
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->validatePostValue();
		}
		$this->addShowWhenForForm();
	}

	protected function validatePostValue() {
		//nothing to do
		return true;
	}

	public function validateRequired() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			//check that a value is set if field is required
			if (isset($this->field->attribute_required)) {
				if (!(isset($this->field->isDisabled)) || ($this->field->isDisabled === false)) {
					if (!((isset($this->field->attribute_checked)) && ($this->field->attribute_checked == "checked"))) {
						$this->field->isValid = false;
						$error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED_CHECKBOX', $this->field->label);
						$this->setErrors($error);
					}
				}
			}
		}
		//validate unique field value in database
		$this->validateUniqueValue();
		return $this->field;
	}

	public function setFieldValueProperties() {
		//stored (validated) "userinput" in new parameter
		$this->field->userInput = $this->getUserInputForJs();
		//Used to determine whether a conditional field of type calculation is disabled
		//Necessary because the calculation code cannot use the attribute_check property which is already reset to the default values at this point of the process
		if (property_exists($this->field, 'attribute_checked')) {
			$this->field->user_checked_state = $this->field->attribute_checked;
		}
		else {
			$this->field->user_checked_state = 'unchecked';
		}
		//set value, which is first displayed, to the configuration defaults
		if (($this->field->configurationDefault === "checked")) {
			$this->field->attribute_checked = $this->field->configurationDefault;
		}
		else {
			if (property_exists($this->field, 'attribute_checked')) {
				unset($this->field->attribute_checked);
			}
		}
		//only used in business calculation if the field is disabled. Use the unchecked value then.
		$this->field->calculationValue = ($this->field->configurationDefault === "checked") ? $this->field->attribute_value : ((isset($this->field->unchecked_value)) ? $this->field->unchecked_value : 0);
		return $this->field;
	}

	private function getUserInputForJs() {
		$task = JFactory::getApplication()->input->getCmd('task', '');
		$value = (isset($this->field->attribute_checked)) ? true : false;
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if ((!isset($_POST[$this->field->name])) && (!empty($this->field->isDisabled))) {
				//if field was originally not disabled use dbValue
				$fieldsdisabledstate = JFactory::getApplication()->getUserState('com_visforms.fieldsdisabledstate.' . $this->form->context, null);
				if (!empty($fieldsdisabledstate) && (is_array($fieldsdisabledstate)) && (empty($fieldsdisabledstate[$this->field->name])) && isset($this->field->editValueChecked)) {
					$value = ($this->field->editValueChecked === "checked") ? true : false;
				}
				else {
					$value = ($this->field->configurationDefault === "checked") ? true : false;
				}
			}
		}
		else if ($task === 'editdata') {
			if ((!empty($this->field->isDisabled))) {
				$value = ($this->field->configurationDefault === "checked") ? true : false;
			}
		}
		return $value;
	}
}