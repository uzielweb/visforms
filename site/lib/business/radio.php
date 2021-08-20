<?php
/**
 * Visforms field radio business class
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

class VisformsBusinessRadio extends VisformsBusiness
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
		//validate unique field value in database
		$this->validateUniqueValue();
	}

	public function validateRequired() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			//check that on option is selected if field is required        
			if (isset($this->field->attribute_required)) {
				//only for fields that are not disabled
				if (!isset($this->field->isDisabled) || ($this->field->isDisabled === false)) {
					$optionSelected = false;
					foreach ($this->field->opts as $opt) {
						if (isset($opt['selected']) && ($opt['selected'] == true)) {
							$optionSelected = true;
							break;
						}
					}
					if ($optionSelected == false) {
						$this->field->isValid = false;
						$error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED_RADIO_SELECT', $this->field->label);
						//attach error to form
						$this->setErrors($error);
					}
				}
			}
		}
		return $this->field;
	}

	protected function validateUniqueValue() {
		$valid = parent::validateUniqueValue();
		if (empty($valid)) {
			$this->disableOption();
		}
	}

	protected function disableOption() {
		$value = $this->field->dbValue;
		$ocount = count($this->field->opts);
		{
			for ($j = 0; $j < $ocount; $j++) {
				if ($this->field->opts[$j]['value'] === $value) {
					$this->field->opts[$j]['disabled'] = true;
					$this->field->opts[$j]['selected'] = false;
				}
			}
		}
	}

	/**
	 * we always use the configuration defaults as field "value" (attribute value, attribute selected, attribute checked or text in textarea)
	 * only then, we can reset the field properly
	 * we use javascript to set field "value state" (val(), prop selected, checked...) to the proper value (user input, configuration default...)
	 */
	public function setFieldValueProperties() {
		//stored (validated) "userinput" in new parameter
		$this->field->userInput = $this->getUserInputForJs();
		//Used to determine whether a conditional field of type calculation is disabled
		//Necessary because the calculation code cannot use the opts array which is already reset to the default values at this point of the process
		$this->field->user_selected_opts = $this->field->opts;
		//set value, which is first displayed to the configuration defaults
		$this->field->opts = $this->field->configurationDefault;
		//only used in business calculation if the field is disabled. Use the configuration default then.
		$this->setCalculationValue();
		return $this->field;
	}

	private function setCalculationValue() {
		//calculationValue is only used in business calculation if the field is disabled. Use the unchecked value then.
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		foreach ($this->field->opts as $opt) {
			if (!empty($opt['selected']) && (isset($opt['value'])) && (is_numeric($opt['value']))) {
				$this->field->calculationValue = $opt['value'];
				//we cannot use more than one value in a calculation!
				break;
			}
		}
		if (!isset($this->field->calculationValue)) {
			$this->field->calculationValue = (isset($this->field->unchecked_value)) ? $this->field->unchecked_value : 0;
		}
	}

	protected function getUserInputForJs() {
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		$options = $this->field->opts;
		$task = JFactory::getApplication()->input->getCmd('task', '');
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if ((!isset($_POST[$this->field->name])) && (!empty($this->field->isDisabled))) {
				if ((!empty($this->field->isDisabled))) {
					//if field was originally not disabled use opts set according to dbValue
					$fieldsdisabledstate = JFactory::getApplication()->getUserState('com_visforms.fieldsdisabledstate.' . $this->form->context, null);
					if (!empty($fieldsdisabledstate) && (is_array($fieldsdisabledstate)) && (empty($fieldsdisabledstate[$this->field->name])) && isset($this->field->editValueOpts)) {
						$options = $this->field->editValueOpts;
					} else {
						$options = $this->field->configurationDefault;
					}
				}
			}
		} else if ($task === 'editdata') {
			if ((!empty($this->field->isDisabled))) {
				$options = $this->field->configurationDefault;
			}
		}
		foreach ($options as $opt) {
			if (!empty($opt['selected']) && (isset($opt['value']))) {
				return $opt['value'];
				//we cannot use more than one value in a calculation!
			}
		}
		//no selected option found, return empty string
		return "";
	}
}