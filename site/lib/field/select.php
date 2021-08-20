<?php
/**
 * Visforms field select class
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

class VisformsFieldSelect extends VisformsField
{
	public function __construct($field, $form) {
		parent::__construct($field, $form);
		//store potentiall query Values for this field in the session
		$this->setQueryValue();
		$this->postValue = $this->input->post->get($field->name, array(), 'ARRAY');
	}

	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->setIsConditional();
		$this->setIsDisplayChanger();
		$this->getOptions();
		$this->disableUsedOptsOnUniqueValues();
		$this->removeInvalidQueryValues();
		$this->setEditValue();
		$this->setConfigurationDefault();
		$this->setEditOnlyFieldDbValue();
		$this->setFieldDefaultValue();
		$this->setDbValue();
		$this->setRedirectParam();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->mendInvalidUncheckedValue();
		$this->setShowRequiredAsterix();
		$this->hasSearchableSelect();
	}

	protected function setFieldDefaultValue() {
		$field = $this->field;
		if ($this->input->getCmd('task', '') == 'editdata') {
			if (isset($this->field->editValue)) {
				$this->setSelectedOptions($this->field->editValue);
			}
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if (isset($_POST[$field->name])) {
				$this->validateUserInput('postValue');
				$this->setSelectedOptions($this->postValue);
			} 
			else {
				//field was disabled, or no chechbox was checked, unselect all
				$this->setSelectedOptions(array());
			}
			$this->field->dataSource = 'post';
			return;
		}

		//if we have a GET Value and field may use GET values, we uses this
		if (isset($field->allowurlparam) && ($field->allowurlparam == true)) {
			$urlparams = JFactory::getApplication()->getUserState('com_visforms.urlparams.' . $this->form->context, null);
			if (!empty($urlparams) && (is_array($urlparams)) && (isset($urlparams[$this->field->name]))) {
				$queryValue = $urlparams[$this->field->name];
			}
			//value is validated so it is a not empty array which is not empty
			if (isset($queryValue)) {
				$this->setSelectedOptions($queryValue);
				$this->field->dataSource = 'query';
				return;
			}
		}
		//we use default values
		return;
	}

	protected function getOptions() {
		//No Options for select given
		if (!(isset($this->field->list_hidden)) || $this->field->list_hidden == "") {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		//split options into an array
		//all values are trimmed
		$opts = JHtml::_('Visformsselect.extractHiddenList', $this->field->list_hidden);
		if (!is_array($opts)) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		$this->field->opts = $opts;
	}

	private function setSelectedOptions($values) {
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		$optsNew = array();
		//we set options
		foreach ($this->field->opts as $opt) {
			//values of disabled opt in queryValue and postValue are already sliced, but not in editValue
			if (in_array($opt['value'], $values) && empty($opt['disabled'])) {
				$opt['selected'] = true;
			} else {
				$opt['selected'] = false;
			}
			$optsNew[] = $opt;
		}
		$this->field->opts = $optsNew;
	}

	protected function setDbValue() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->field->dbValue = implode(JHtmlVisformsselect::$msdbseparator, $this->postValue);
		}
	}

	protected function setEditOnlyFieldDbValue() {
		foreach ($this->field->configurationDefault as $opt) {
			if ($opt['selected']) {
				$this->field->editOnlyFieldDbValue = $opt['value'];
				break;
			}
		}
	}

	protected function validateUserInput($inputType) {
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		//Array of values set by user
		$values = $this->$inputType;
		if (is_array($values)) {
			//it is not necessary to run filter "string" on $word, because the validate function will remove any user input that is not a valid select option
			//and we assume, that the options are clean strings
			//$clean = $this->input->filter->clean($word, "STRING");
			//there should be not white spaces in word, because field options are trimmed before the field is displayed
			foreach ($values as $index => $word) {
				$values[$index] = (string) $word;
			}
		}

		//Array of options set in field definition
		$opts = $this->field->opts;

		//array of values allowed by field settings
		$allowedValues = array_map(function ($element) {
			return (empty($element['disabled'])) ? $element['value'] : (string) '';
		}, $opts);
		//when we deal with a select that is not required, we may hat an empty string submitted by post which is valid but not part of the option list
		array_push($allowedValues, '');

		//are there any values in the post which are not allowed?
		$diffs = array_diff($values, $allowedValues);
		if (count($diffs) > 0) {
			//we have an invalid value in post
			$this->field->isValid = false;
			$error = JText::sprintf('COM_VISFORMS_OPTION_HAS_INVALID_POST_VALUE', $this->field->label);
			$this->setErrorMessageInForm($error);
		}

		//Remove invalid value from user input array, so that it might not accidentally be stored in the database
		foreach ($diffs as $diff) {
			$key = array_keys($values, $diff);
			array_splice($this->$inputType, $key[0], 1);
		}
	}

	protected function disableUsedOptsOnUniqueValues() {
		if (empty($this->field->uniquevaluesonly)) {
			return true;
		}
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Select must have at least one option.');
		}
		$recordId = (!empty($this->field->recordId)) ? $this->field->recordId : 0;
		if (isset($this->field->id) && is_numeric($this->field->id)) {
			$usedOpts = JHtml::_('visformsselect.getStoredUserInputs', $this->field->id, $this->form->id, $recordId);
		}
		if (empty($usedOpts)) {
			return true;
		}
		$optsNew = array();
		$usedOptsValues = array();
		if (!empty($usedOpts)) {
			foreach ($usedOpts as $usedOpt) {
				$usedOptValues = JHtmlVisformsselect::explodeMsDbValue($usedOpt);
				foreach ($usedOptValues as $usedOptValue) {
					$usedOptsValues[] = $usedOptValue;
				}
			}
		}

		foreach ($this->field->opts as $opt) {
			if (in_array($opt['value'], $usedOptsValues)) {
				$opt['disabled'] = true;
				$opt['selected'] = false;
			}
			$optsNew[] = $opt;
		}
		$this->field->opts = $optsNew;
	}

	protected function setRedirectParam() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post' && (!empty($this->field->addtoredirecturl))) {
			$this->field->redirectParam = $this->postValue;
		}
	}

	protected function setQueryValue() {
		if ($this->form->displayState === VisformsModelVisforms::$displayStateIsNew) {
			$app = JFactory::getApplication();
			$task = $app->input->getCmd('task', '');
			if (($task !== 'editdata') && ($task !== 'saveedit')) {
				//using $this->input->get->get makes sure that the joomla! security functions are performed on the user inputs!
				//plugin form view sets get values as well
				$queryValue = $this->input->get->get($this->field->name, null, 'ARRAY');
				//make sure, that input get values for selects are stored as array!
				$this->input->get->set($this->field->name, $queryValue);
				if (!is_null($queryValue)) {
					$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->form->context);
					if (empty($urlparams)) {
						$urlparams = array();
					}
					$urlparams[$this->field->name] = $queryValue;
					$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
				}
			}
		}
	}

	protected function removeInvalidQueryValues() {
		$app = JFactory::getApplication();
		$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->form->context);
		if (empty($urlparams) || !is_array($urlparams) || !isset($urlparams[$this->field->name])) {
			return;
		}
		$queryValue = $urlparams[$this->field->name];
		if (isset($queryValue)) {
			//invalid format
			//ToDo: check the following: we allow empty query values for all other field types, So do it here, too?? then remove second condition
			if ((!is_array($queryValue)) || (!(count($queryValue) > 0))) {
				//remove invalid queryValue ulrparams array and set urlparams to Null if the array is empty
				unset($urlparams[$this->field->name]);
				if (!(count($urlparams) > 0)) {
					$urlparams = null;
				}
				$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
				return;
			}
			if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
				throw new InvalidArgumentException ('Select must have at least one option.');
			}
			foreach ($queryValue as $index => $word) {
				$queryValue[$index] = (string) trim($word);
			}
			//Array of options set in field definition
			$opts = $this->field->opts;

			//array of values allowed by field settings which are not disabled
			$allowedValues = array_map(function ($element) {
				return (empty($element['disabled'])) ? $element['value'] : (string) '';
			}, $opts);
			//when we deal with a select that is not required, we may hat an empty string submitted by post which is valid but not part of the option list
			array_push($allowedValues, (string) '');

			//are there any values in the post which are not allowed?
			$diffs = array_diff($queryValue, $allowedValues);

			//Remove invalid value from query value array, so that it might not accidentally be stored in the database
			foreach ($diffs as $diff) {
				$key = array_keys($queryValue, $diff);
				array_splice($queryValue, $key[0], 1);
			}

			if (count($queryValue) > 0) {
				$urlparams[$this->field->name] = $queryValue;
			} else {
				//remove invalid queryValue ulrparams array and set urlparams to Null if the array is empty
				unset($urlparams[$this->field->name]);
				if (!(count($urlparams) > 0)) {
					$urlparams = null;
				}
			}
			$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
		}
	}

	protected function setConfigurationDefault() {
		$orgOpts = $this->field->opts;
		$task = $this->input->getCmd('task', '');
		if (($task !== 'editdata') && ($task !== 'saveedit')) {
			$urlparams = JFactory::getApplication()->getUserState('com_visforms.urlparams.' . $this->form->context, null);
			if (!empty($urlparams) && (is_array($urlparams)) && (isset($urlparams[$this->field->name]))) {
				$queryValue = $urlparams[$this->field->name];
			}
			//if form was originally called with valid url params, reset to this url params
			if (isset($this->field->allowurlparam) && ($this->field->allowurlparam == true) && isset($queryValue)) {
				$this->setSelectedOptions($queryValue);
			}
		}
		$this->field->configurationDefault = $this->field->opts;
		$this->field->opts = $orgOpts;
	}

	protected function setEditValue() {
		$task = $this->input->getCmd('task', '');
		if (($task === 'editdata') || ($task === 'saveedit')) {
			$editValue = "";
			$data = $this->form->data;
			$datafieldname = $this->getParameterFieldNameForEditValue();
			if (isset($data->$datafieldname)) {
				$filter = JFilterInput::getInstance();
				$editValue = $filter->clean($data->$datafieldname, 'STRING');
			}
			$this->field->editValue = JHtmlVisformsselect::explodeMsDbValue($editValue);
			//store an options array created from the editValue settings with the field for later use
			$orgOpts = $this->field->opts;
			$this->setSelectedOptions($this->field->editValue);
			$this->field->editValueOpts = $this->field->opts;
			$this->field->opts = $orgOpts;
		}
	}
}