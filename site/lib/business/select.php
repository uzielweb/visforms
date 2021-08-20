<?php
/**
 * Visforms field select business class
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

class VisformsBusinessSelect extends VisformsBusiness
{
	public function getFields() {
		$this->setField();
		return $this->fields;
	}

	protected function setField() {
		$this->setCustomJs();
		$this->setIsDisabled();
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->validatePostValue();
		}
		$this->addShowWhenForForm();
	}

	protected function validatePostValue() {
		//rules for selects are: maxcount
		//update $this->field with value from $this->fields
		$this->updateField();
		$app = JFactory::getApplication();
		$valid = true;
		//check that we do not have to many selected values in user input
		if (!(isset($this->field->attribute_multiple)) || ($this->field->attribute_multiple == false)) {
			$maxcount = 1;
			$count = 0;
			//get count ouf selected options
			foreach ($this->field->opts as $opt) {
				if (isset($opt['selected']) && ($opt['selected'] == true)) {
					$count++;
				}
			}
			if (VisformsValidate::validate('max', array('count' => $count, 'maxcount' => $maxcount)) == false) {
				//invalid value
				$valid = false;
				$error = JText::sprintf('COM_VISFORMS_FIELD_MAX_LENGTH_MULTICHECKBOX', $maxcount, $this->field->label);
				//attach error to form
				$this->setErrors($error);
				//only the last option will be displayed as selected in form
				//set selected to false except for the last selected option,
				$optCount = count($this->field->opts);
				for ($i = 0; $i < $optCount; $i++) {
					//unselect option
					if (isset($this->field->opts[$i]['selected']) && ($this->field->opts[$i]['selected'] == true) && $count != 1) {
						$this->field->opts[$i]['selected'] = false;
						$count--;
					}
					//perform additional things, which may be necessary because of the wrong amount of selected values, when we reach the last option
					if ($i == ($optCount - 1)) {
						if (isset($this->field->isDisplayChanger) && ($this->field->isDisplayChanger == true)) {
							//mend isDisabled property in all depending fields (setIsDisabeld() is recursive)
							foreach ($this->fields as $child) {
								//only check for fields that are not $this->field
								if ($child->id != $this->field->id) {
									$this->setIsDisabled($child);
								}
							}
							break;
						}
						else {
							break;
						}
					}
				}
			}
		}
		//validate unique field value in database
		$this->validateUniqueValue();
		//at least one validation failed
		if (!$valid) {
			$this->field->isValid = false;
		}
	}

	public function validateRequired() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$app = JFactory::getApplication();
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
		if (empty($this->form->saveresult)) {
			return true;
		}
		//validate unique field value in database
		if ((!empty($this->field->uniquevaluesonly)) && (!empty($this->field->dbValue))) {
			//get values of all recordsets in datatable
			$details = array();
			$db = JFactory::getDbO();
			if (isset($this->field->id) && is_numeric($this->field->id)) {
				$query = $db->getQuery(true);
				$query->select($db->qn('F' . $this->field->id))
					->from($db->qn('#__visforms_' . $this->form->id));
				if (!empty($this->field->uniquepublishedvaluesonly)) {
					$query->where($db->qn('published') . ' = ' . 1);
				}
				if (!empty($this->field->recordId)) {
					$query->where($db->qn('id') . ' != ' . $this->field->recordId);
				}
				$formSelections = JHtmlVisformsselect::explodeMsDbValue($this->field->dbValue);
				$storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$msdbseparator), $db->quoteName('F' . $this->field->id), $db->q(JHtmlVisformsselect::$msdbseparator)));
				foreach ($formSelections as $formselection) {
					$formselection = '%' . JHtmlVisformsselect::$msdbseparator . $formselection . JHtmlVisformsselect::$msdbseparator . '%';
					$query->where('(' . $storedSelections . ' like ' . $db->q($formselection) . ')');
				}
				try {
					$db->setQuery($query);
					$details = $db->loadColumn();
				}
				catch (Exception $exc) {
					return true;
				}
			}
			//check if there is a match
			if (!empty($details)) {
				$this->field->isValid = false;
				$this->disableOption($formSelections);
				$error = JText::sprintf('COM_VISFORMS_UNIQUE_VALUE_REQUIRED', $this->field->label, $this->field->dbValue);
				//attach error to form
				$this->setErrors($error);
				return false;
			}
		}
		return true;
	}

	protected function disableOption($value) {
		$ocount = count($this->field->opts);
		{
			for ($j = 0; $j < $ocount; $j++) {
				if (in_array($this->field->opts[$j]['value'], $value)) {
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
		$userInputs = array();
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		$options = $this->field->opts;
		$task = JFactory::getApplication()->input->getCmd('task', '');
		//$this->field->opts is set on the presumption, that the field was enabled
		//if field was disabled we have to use the configuration default as user input
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if ((!isset($_POST[$this->field->name])) && (!empty($this->field->isDisabled))) {
				if ((!empty($this->field->isDisabled))) {
					//if field was originally not disabled use opts set according to dbValue
					$fieldsdisabledstate = JFactory::getApplication()->getUserState('com_visforms.fieldsdisabledstate.' . $this->form->context, null);
					if (!empty($fieldsdisabledstate) && (is_array($fieldsdisabledstate)) && (empty($fieldsdisabledstate[$this->field->name])) && isset($this->field->editValueOpts)) {
						$options = $this->field->editValueOpts;
					}
					else {
						$options = $this->field->configurationDefault;
					}
				}
			}
		}
		else if ($task === 'editdata') {
			if ((!empty($this->field->isDisabled))) {
				$options = $this->field->configurationDefault;
			}
		}
		foreach ($options as $opt) {
			if (!empty($opt['selected']) && (isset($opt['value']))) {
				$userInputs[] = $opt['value'];
			}
		}
		if (empty($userInputs)) {
			$userInputs[] = "";
		}
		return $userInputs;
	}

	protected function setCustomJs() {
		// add searchbox to field
		if (empty($this->field->is_searchable)) {
			return true;
		}
		$script = 'jQuery(document).ready(function () {jQuery("#field' . $this->field->id . '").select2({width: "computedstyle"});});';
		$this->field->customJs[] = $script;
	}
}