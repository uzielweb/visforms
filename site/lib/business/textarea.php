<?php
/**
 * Visforms field textarea business class
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
use Joomla\String\StringHelper;

class VisformsBusinessTextarea extends VisformsBusiness
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
		//rules for textarea are: minlength, maxlength, unique value
		//update $this->field with value from $this->fields
		$this->updateField();
		$valid = true;
		//only to perform when the value is not empty
		if ($this->field->initvalue != "") {
			//check for right minlength
			if ((isset($this->field->validate_minlength)) && ($this->field->validate_minlength != '')) {
				$mincount = $this->field->validate_minlength;
				$count = StringHelper::strlen($this->field->initvalue);
				if (VisformsValidate::validate('min', array('count' => $count, 'mincount' => $mincount)) == false) {
					//invalid value
					$valid = false;
					$error = JText::sprintf('COM_VISFORMS_FIELD_MIN_LENGTH', $this->field->label, $mincount);
					//attach error to form
					$this->setErrors($error);
				}
			}

			//check for right maxlength
			if ((isset($this->field->attribute_maxlength)) && ($this->field->attribute_maxlength != '')) {
				$maxcount = $this->field->attribute_maxlength;
				$count = StringHelper::strlen($this->field->initvalue);
				if (VisformsValidate::validate('max', array('count' => $count, 'maxcount' => $maxcount)) == false) {
					//invalid value
					$valid = false;
					$error = JText::sprintf('COM_VISFORMS_FIELD_MAX_LENGTH', $this->field->label, $maxcount);
					//attach error to form
					$this->setErrors($error);
				}
			}
			//validate unique field value in database
			$this->validateUniqueValue();
		}

		//at least one validation failed
		if (!$valid) {
			$this->field->isValid = false;
		}
	}

	public function validateRequired() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			//check that a value is set if field is required
			if (isset($this->field->attribute_required)) {
				if (!(isset($this->field->isDisabled)) || ($this->field->isDisabled === false)) {
					if (VisformsValidate::validate('notempty', array('value' => $this->field->initvalue)) == false) {
						$this->field->isValid = false;
						$error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED', $this->field->label);
						//attach error to form
						$this->setErrors($error);
					}
				}
			}
		}
		return $this->field;
	}

	/**
	 * we always use the configuration defaults as field "value" (attribute value, attribute selected, attribute checked or text in textarea)
	 * only then, we can reset the field properly
	 * we use javascript to set field "value state" (val(), prop selected, checked...) to the proper value (user input, configuration default...)
	 */
	public function setFieldValueProperties() {
		//stored (validated) "userinput" in new parameter
		$this->field->userInput = $this->getUserInputForJs();
		//set value, which is first displayed to the configuration defaults
		$this->field->initvalue = $this->field->configurationDefault;
		return $this->field;
	}

	private function getUserInputForJs() {
		$task = JFactory::getApplication()->input->getCmd('task', '');
		$value = $this->field->initvalue;
		if ($task === 'editdata') {
			//use configuartion default as default, if field is disabled
			if ((!empty($this->field->isDisabled))) {
				$value = $this->field->configurationDefault;
			}
		}
		if ($task === 'saveedit') {
			if ((!empty($this->field->isDisabled))) {
				//if field was originally not disabled use dbValue
				$fieldsdisabledstate = JFactory::getApplication()->getUserState('com_visforms.fieldsdisabledstate.' . $this->form->context, null);
				if (!empty($fieldsdisabledstate) && (is_array($fieldsdisabledstate)) && (empty($fieldsdisabledstate[$this->field->name])) && isset($this->field->editValue)) {
					$value = $this->field->editValue;
				} else {
					$value = $this->field->configurationDefault;
				}
			}
		}
		//Do not use single quotes with \n\r!
        return str_replace(array('@'), array('&#64'), $value);
	}
}