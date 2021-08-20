<?php
/**
 * Visforms business logic class
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

abstract class VisformsBusiness
{
	protected $type;
	protected $field;
	protected $fields;
	protected $form;

	public function __construct($field, $form, $fields) {
		$this->type = $field->typefield;
		$this->field = $field;
		$this->form = $form;
		$this->fields = $fields;
		$this->input = JFactory::getApplication()->input;
	}

	public static function getInstance($field, $form, $fields) {
		if (!(isset($field->typefield))) {
			return false;
		}

		$classname = get_called_class() . ucfirst($field->typefield);
		if (!class_exists($classname)) {
			//try to register it
			JLoader::register($classname, dirname(__FILE__) . '/business/' . $field->typefield . '.php');
			if (!class_exists($classname)) {
				//return a default class?
				return false;
			}
		}
		//delegate to the appropriate subclass
		return new $classname($field, $form, $fields);
	}

	public function setFieldValueProperties() {
		return $this->field;
	}

	// Store the original disabled state of a field as it results from the stored user inputs in the session
	public function setOrgDisabledStateFromStoredDataInUserState() {
		if ($this->form->displayState === VisformsModelVisforms::$displayStateIsNewEditData) {
			$app = JFactory::getApplication();
			//using $this->input->get->get makes sure that the joomla! security functions are performed on the user inputs!
			//plugin form view sets get values as well

			if (!empty($this->field->isDisabled)) {
				$disabledStates = $app->getUserState('com_visforms.fieldsdisabledstate.' . $this->form->context);
				if (empty($disabledStates)) {
					$disabledStates = array();
				}
				$disabledStates[$this->field->name] = $this->field->isDisabled;
				$app->setUserState('com_visforms.fieldsdisabledstate.' . $this->form->context, $disabledStates);
			}
		}
	}

	protected function setIsDisabled($field = null, $alreadyChecked = array()) {
		if (is_null($field)) {
			$self = true;
			$field = $this->field;
		}
		//we only have to check fields that are conditional and not already checked
		if (isset($field->isConditional) && ($field->isConditional == true) && (!(in_array($field->id, $alreadyChecked)))) {
			foreach ($field as $name => $value) {
				//find condition and set isDisabled in field
				if (strpos($name, 'showWhen') !== false) {
					$field->isDisabled = $this->showWhenValueIsNotSelected($value);
				}

			}
			//push modified field back into fields array
			$this->updateFieldsArray($field);

			//if field is disabled we have to check if it is a displayChanger and have to adapt the isDisabled in all fields that are restricted by this fields
			if ((isset($field->isDisabled) && ($field->isDisabled == true)) && (isset($this->field->isDisplayChanger) && ($this->field->isDisplayChanger == true))) {
				//add field id to already checked array
				$alreadyChecked[] = $field->id;

				//get id's of restricted fields
				$children = array();
				if (isset($field->restrictions['usedAsShowWhen'])) {
					foreach ($field->restrictions['usedAsShowWhen'] as $restrictedFieldId) {
						$children[] = $restrictedFieldId;
					}
				}

				//loop through restricted field id's
				foreach ($children as $childid) {
					//loop through field object
					foreach ($this->fields as $childfield) {
						//find matching field in fields (if available)
						//and prevent infinit loops
						if (($childid == $childfield->id) && ($field->id != $childfield->id) && (!(in_array($childfield->id, $alreadyChecked)))) {
							$this->setIsDisabled($childfield, $alreadyChecked);
						}
					}
				}
			}
		}
	}

	//Check if a value of a showWhen restict set in one field is not selected
	protected function showWhenValueIsNotSelected($avalue) {
		$fields = $this->fields;
		foreach ($avalue as $value) {
			if (preg_match('/^field/', $value) === 1) {
				$restrict = explode('__', $value, 2);
				//get id of field which can activate to show the conditional field
				$fieldId = JHtml::_('visforms.getRestrictedId', $restrict[0]);
				//get value that has to be selected in the field that can activate to show the conditional field
				$rvalue = $restrict[1];
				foreach ($fields as $field) {
					//restricting field, if this field is disable we hide the restricted field too
					if (($field->id == $fieldId) && (!(isset($field->isDisabled)) || ($field->isDisabled == false))) {
						switch ($field->typefield) {
							case 'select' :
							case 'radio' :
							case 'multicheckbox' :
								// fields of type calcualtion are precessed after all other fields are finished
								// the options array of these fields is already reset to the default values
								// selections which the user has maded are stored in the user_selected_opts array
								$opts = (isset($field->user_selected_opts))? $field->user_selected_opts : $field->opts;
								foreach ($opts as $opt) {
									if (($opt['selected'] == true) && ($opt['id'] == $rvalue)) {
										return false;
									}
								}
								break;
							case 'checkbox' :
								// fields of type calcualtion are precessed after all other fields are finished
								// the checkbox state of these fields is already reset to the default values
								// selections which the user has maded are stored in the user_checked_state array
								if (isset($field->user_checked_state)) {
									if ($field->user_checked_state === 'checked') {
										return false;
									}
								}
								else if (isset($field->attribute_checked) && ($field->attribute_checked == 'checked')) {
									return false;
								}
								break;
							default :
								break;
						}
					}
				}
			}
		}
		return true;
	}

	protected function updateFieldsArray($field) {
		$n = count($this->fields);
		for ($i = 0; $i < $n; $i++) {
			if ($this->fields[$i]->id == $field->id) {
				$this->fields[$i] = $field;
			}
		}
	}

	protected function updateField() {
		$n = count($this->fields);
		for ($i = 0; $i < $n; $i++) {
			if ($this->field->id == $this->fields[$i]->id) {
				$this->field = $this->fields[$i];
			}
		}
	}

	protected function validateUniqueValue() {
		if (empty($this->form->saveresult)) {
			return true;
		}
		//validate unique field value in database (only if user has submitted a value)
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
				$query->where($db->qn('F' . $this->field->id) . ' = ' . $db->q($this->field->dbValue));
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
				$error = JText::sprintf('COM_VISFORMS_UNIQUE_VALUE_REQUIRED', $this->field->label, $this->field->dbValue);
				//attach error to form
				$this->setErrors($error);
				return false;
			}
		}
		return true;
	}

	// Make property showWhen usable in form display (for administration we store fieldId and optionId, for form we want fieldId and OptionValue)
	protected function addShowWhenForForm() {
		$field = $this->field;
		if (isset($field->showWhen) && (is_array($field->showWhen) && count($field->showWhen) > 0)) {
			$showWhenForForm = array();
			//showWhen is an array with showWhen options in format fieldN__optId
			//we iterate through all array items
			while (!empty($field->showWhen)) {
				$showWhen = array_shift($field->showWhen);
				//split showWhen option in fieldN and optId
				$parts = explode('__', $showWhen, 2);
				if (count($parts) < 2) {
					//showWhen option has wrong format!
					continue;
				}
				//get Id of restricting field form "fieldN" string
				$restrictorId = JHtml::_('visforms.getRestrictedId', $parts[0]);
				//get the restricting field from fields object
				$restrictor = new stdClass();
				foreach ($this->fields as $rfield) {
					if ($rfield->id == $restrictorId) {
						$restrictor = $rfield;
						break;
					}
				}
				//restricting fields have either an option list (listbox, radio, checkboxgroup) or are checkboxes
				//get the value that matches the optId
				switch ($restrictor->typefield) {
					case 'select' :
					case 'radio' :
					case 'multicheckbox' :
						if (isset($restrictor->opts) && (is_array($restrictor->opts))) {
							foreach ($restrictor->opts as $opt) {
								if ($opt['id'] == $parts[1]) {
									//create an item in showWhenForForm Property using the opt value
									$showWhenForForm[] = $parts[0] . '__' . $opt['value'];
								}
							}
						}
						break;
					case 'checkbox' :
						$showWhenForForm[] = $showWhen;
						break;
					default :
						break;
				}
			}
			if (!empty($showWhenForForm)) {
				$field->showWhenForForm = $showWhenForForm;
			}
			unset($showWhenForForm);
			$this->updateFieldsArray($field);
		}
	}

	protected function setErrors($error) {
		if (!(isset($this->form->errors))) {
			$this->form->errors = array();
		}
		if (is_array($this->form->errors)) {
			array_push($this->form->errors, $error);
		}
	}

	protected function calculate($field = null) {
		if (is_null($field)) {
			$field = $this->field;
		}
		$equation = $field->equation;
		if (empty($equation)) {
			return true;
		}
		if (isset($field->dbValue)) {
			//already calculated
			return true;
		}
		$precision = (int) $field->precision;
		$pattern = '/\[[A-Z0-9]{1}[A-Z0-9\-]*]/';
		$numberpattern = '/^\-?\d+\.?\d*$/';
		$valid = true;
		if (preg_match_all($pattern, $equation, $matches)) {
			//found matches are store in the $matches[0] array
			foreach ($matches[0] as $match) {
				$str = trim($match, '\[]');
				$fieldname = $this->form->context . StringHelper::strtolower($str);
				foreach ($this->fields as $placeholder) {
					if ($placeholder->name == $fieldname) {
						if (($placeholder->typefield == 'calculation') && (!isset($placeholder->dbValue))) {
							self::calculate($placeholder);
						}
						//get value of placeholder field from fields
						//replace comma with dot (if value is formated with comma as decimal separator
						if (!empty($placeholder->isDisabled) && (isset($placeholder->calculationValue))) {
							if ($placeholder->typefield === "date" && !empty($placeholder->calculationValue)) {
								$format = explode(';', $placeholder->format);
								$unifiedFromattedDate = DateTime::createFromFormat($format[0], $placeholder->calculationValue);
								$unifiedFromattedDate->setTimezone(new DateTimeZone("UTC"));
								$unifiedFromattedDate->setTime(0, 0);
								$replace = ($unifiedFromattedDate->getTimestamp() / 86400);
							} else {
								$replace = trim(str_replace(",", ".", $placeholder->calculationValue));
							}
							if (!(preg_match($numberpattern, $replace) == true)) {
								$valid = false;
								$replace = 1;
							}
						} else {
							if (($placeholder->typefield === "checkbox") && ($placeholder->dbValue === "") && (isset($placeholder->unchecked_value))
								&& ($placeholder->unchecked_value !== "")) {
								$replace = trim(str_replace(",", ".", $placeholder->unchecked_value));
							} else if ($placeholder->typefield === "date") {
								if ($placeholder->dbValue === "") {
									$replace = 0;
								} else {
									$format = explode(';', $placeholder->format);
									$unifiedFromattedDate = DateTime::createFromFormat($format[0], $placeholder->dbValue);
									$unifiedFromattedDate->setTimezone(new DateTimeZone("UTC"));
									$unifiedFromattedDate->setTime(0, 0);
									$replace = ($unifiedFromattedDate->getTimestamp() / 86400);
								}
							} else if ($placeholder->dbValue === "" && isset($placeholder->unchecked_value)) {
								$replace = $placeholder->unchecked_value;
							} else {
								//i.e.fieldtype hidden
								$replace = trim(str_replace(",", ".", $placeholder->dbValue));
							}
							if (!(preg_match($numberpattern, $replace) == true)) {
								$valid = false;
								$replace = 1;
							}
						}
						//remove invalid leading 0's
						if (strpos($replace, '.') === false && 0 != $replace) {
							$replace = ltrim($replace, "0");
						}
						$replace = '(' . $replace . ')';
						//replace the matches in equation with dbvalue of placeholder field
						$newEquation = preg_replace('\'' . preg_quote($match) . '\'', $replace, $equation);
						$equation = stripslashes($newEquation);
						break;
					}
				}
			}
		}
		//Only since php 7 there seems to be some sort of error handling for eval (ParseError exception)
		//ToDo test and use this exception
		eval('$res=' . $equation . ';');
		if (!(preg_match($numberpattern, $res) == true)) {
			$valid = false;
			$res = 1;
		}
		$res = round($res, $precision);
		$res = (!empty($field->fixed)) ? number_format($res, $precision, $field->decseparator, '') : (string) $res;
		$res = ((!empty($field->decseparator)) && ($field->decseparator == ",")) ? str_replace('.', ',', $res) : $res;
		$field->dbValue = $res;
		if (empty($valid)) {
			$field->isValid = false;
			$error = JText::sprintf('COM_VISFORMS_FIELD_CAL_INVALID_INPUTS', $field->label);
			//attach error to form
			$this->setErrors($error);
		}
		//push modified field back into fields array
		$this->updateFieldsArray($field);
		return true;
	}

	abstract public function getFields();

	abstract protected function setField();

	abstract protected function validatePostValue();

	abstract public function validateRequired();
}