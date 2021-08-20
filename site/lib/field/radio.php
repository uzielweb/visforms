<?php
/**
 * Visforms field radio class
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

class VisformsFieldRadio extends VisformsField
{
	public function __construct($field, $form) {
		parent::__construct($field, $form);
		//store potentiall query Values for this field in the session
		$this->setQueryValue();
		$this->postValue = $this->input->post->get($field->name, '', 'STRING');
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
		$this->setEnterKeyAction();
		$this->mendInvalidUncheckedValue();
		$this->setShowRequiredAsterix();
	}

	protected function setFieldDefaultValue() {
		$field = $this->field;
		if ($this->input->getCmd('task', '') == 'editdata') {
			if (isset($this->field->editValue)) {
				$this->setSelectedOption($this->field->editValue);
			}
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if (isset($_POST[$field->name])) {
				//validation removes invalide values from $this->postValue!
				$this->validateUserInput('postValue');
				$this->setSelectedOption($this->postValue);
			} //disabled field
			else {
				$this->setSelectedOption("");
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
			if (isset($queryValue)) {
				$this->setSelectedOption($queryValue);
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
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}
		//split options into an array
		$opts = JHtml::_('visformsselect.extractHiddenList', $this->field->list_hidden);
		if (!is_array($opts)) {
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}
		$this->field->opts = $opts;
	}

	protected function setSelectedOption($value) {
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}
		//there should be not white spaces in word, but trim anyway because options are always trimmed, too
		$value = trim($value);
		$optsNew = array();
		//we set options
		foreach ($this->field->opts as $opt) {
			//editValue and postValue may be empty string this should not result in an selected option accidentally
			if (($value !== "") && ($opt['value'] == $value) && (empty($opt['disabled']))) {
				$opt['selected'] = true;
			} 
			else {
				$opt['selected'] = false;
			}
			$optsNew[] = $opt;
		}
		$this->field->opts = $optsNew;
	}

	protected function setDbValue() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->field->dbValue = $this->postValue;
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
		//Array of values set by user
		$value = $this->$inputType;
		//Empty value is valid
		if ((!isset($value)) || ($value === '')) {
			return true;
		}

		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}

		//Array of options set in field definition
		$opts = $this->field->opts;

		//array of values allowed by field settings
		$allowedValues = array_map(function ($element) {
			return (empty($element['disabled'])) ? $element['value'] : (string) '';
		}, $opts);

		//is user input not in allowed options?
		if (!(in_array($value, $allowedValues, true))) {
			//we have an invalid value in post
			$this->field->isValid = false;
			$error = JText::sprintf('COM_VISFORMS_OPTION_HAS_INVALID_POST_VALUE', $this->field->label);
			$this->setErrorMessageInForm($error);
			//remove invalid user input
			$this->$inputType = "";
			return false;
		}

		return true;
	}

	protected function disableUsedOptsOnUniqueValues() {
		if (empty($this->field->uniquevaluesonly)) {
			return true;
		}
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}
		$recordId = (!empty($this->field->recordId)) ? $this->field->recordId : 0;
		if (isset($this->field->id) && is_numeric($this->field->id)) {
			$usedOpts = JHtml::_('visformsselect.getStoredUserInputs', $this->field->id, $this->form->id, $recordId);
		}
		if (empty($usedOpts)) {
			return true;
		}
		$optsNew = array();
		foreach ($this->field->opts as $opt) {
			if (in_array($opt['value'], $usedOpts)) {
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

	protected function removeInvalidQueryValues() {
		$app = JFactory::getApplication();
		$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->form->context);
		if (empty($urlparams) || !is_array($urlparams) || !isset($urlparams[$this->field->name])) {
			return;
		}
		$queryValue = $urlparams[$this->field->name];
		//empty query Value is allowed
		if ($queryValue === '') {
			return;
		}
		if (!isset($this->field->opts) || !(is_array($this->field->opts))) {
			throw new InvalidArgumentException ('Radio must have at least one option.');
		}
		//Array of options set in field definition
		$opts = $this->field->opts;

		//array of values allowed by field settings
		$allowedValues = array_map(function ($element) {
			//return $element['value'];
			return (empty($element['disabled'])) ? $element['value'] : (string) '';
		}, $opts);

		//is user input not in allowed options?
		if (!(in_array($queryValue, $allowedValues, true))) {
			//remove invalid queryValue ulrparams array and set urlparams to Null if the array is empty
			unset($urlparams[$this->field->name]);
			if (!(count($urlparams) > 0)) {
				$urlparams = null;
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
				$this->setSelectedOption($queryValue);
			}
		}
		$this->field->configurationDefault = $this->field->opts;
		$this->field->opts = $orgOpts;
	}

	protected function setEditValue() {
		$task = $this->input->getCmd('task', '');
		if (($task === 'editdata') || ($task === 'saveedit')) {
			$this->field->editValue = "";
			$data = $this->form->data;
			$datafieldname = $this->getParameterFieldNameForEditValue();
			if (isset($data->$datafieldname)) {
				$filter = JFilterInput::getInstance();
				$this->field->editValue = $filter->clean($data->$datafieldname, 'STRING');
			}
			//store an options array created from the editValue settings with the field for later use
			$orgOpts = $this->field->opts;
			$this->setSelectedOption($this->field->editValue);
			$this->field->editValueOpts = $this->field->opts;
			$this->field->opts = $orgOpts;
		}
	}
}