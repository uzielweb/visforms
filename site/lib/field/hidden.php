<?php
/**
 * Visforms field hidden class
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

class VisformsFieldHidden extends VisformsField
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
		$fillWith = $this->fillWith();
		if ($fillWith !== false) {
			//if we have a special default value set in field declaration we use this
			$this->field->attribute_value = $fillWith;
		}
		$this->setEditValue();
		$this->setConfigurationDefault();
		$this->setEditOnlyFieldDbValue();
		$this->setFieldDefaultValue();
		$this->setDbValue();
		$this->setRedirectParam();
		$this->setCustomJs();
	}

	protected function setFieldDefaultValue() {
		$field = $this->field;

		if ($this->input->getCmd('task', '') == 'editdata') {
			if ((isset($this->field->editValue))) {
				$this->field->attribute_value = $this->field->editValue;
			}
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			//this will create a error message on form display
			$this->validateUserInput('postValue');
			if (isset($_POST[$field->name])) {
				$this->field->attribute_value = $this->postValue;
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
				$this->field->attribute_value = $queryValue;
				$this->field->dataSource = 'query';
				return;
			}
		}
		//Nothing to do
		return;
	}

	protected function validateUserInput($inputType) {
		$type = $this->type;
		$value = $this->$inputType;
		//we can't validate the user input, if the field is filled with a unigue value by default
		if (!empty($this->field->filluid)) {
			return;
		}
		//only check, that user input === attribute_value if a attribute_value is set (!=="")
		if ((!isset($this->field->attribute_value)) || ($this->field->attribute_value === '')) {
			return true;
		}
		//user input must match the attribute value; if not user input is set this is invalide, too
		if (isset($value) && VisformsValidate::validate('equalto', array('value' => $value, 'cvalue' => $this->field->configurationDefault))) {
			return;
		} 
		else {
			//invalid user inputs - set field->isValid to false
			$this->field->isValid = false;
			//set the Error Message
			$error = JText::sprintf('COM_VISFORMS_INVALID_HIDDEN_FIELD_USER_INPUT_DOES_NOT_MATCH_DEFAULT', $this->field->label);
			$this->setErrorMessageInForm($error);
			return;
		}
	}

	protected function setDbValue() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->field->dbValue = $this->postValue;
		}
	}

	protected function setEditOnlyFieldDbValue() {
		$this->field->editOnlyFieldDbValue = $this->field->configurationDefault;
	}

	protected function setRedirectParam() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post' && (!empty($this->field->addtoredirecturl))) {
			$this->field->redirectParam = $this->postValue;
		}
	}

	protected function fillWith() {
		//if we have a special default value set in field declaration we use this
		$field = $this->field;
		if ((!empty($field->filluid))) {
			$task = $this->input->getCmd('task', '');
			//if we edit stored user inputs we want to use the user profile of the user who submitted the form as default fill with values
			if (($task === 'editdata') || ($task === 'saveedit')) {
				$data = $this->form->data;
				$userId = $data->created_by;
				if (!empty($userId)) {
					$user = JFactory::getUser($userId);
				}
			} //use user profile of logged in user
			else {
				$user = JFactory::getUser();
				$userId = $user->get('id');
			}
			// this default value does not depent on the user being logged in
			if ($field->filluid == "1") {
				return uniqid($this->field->attribute_value, true);
			}
			if ($userId != 0) {
				if ($field->filluid == "1") {
					return uniqid($this->field->attribute_value, true);
				}
				if ($field->filluid == "2") {
					return $user->get('name');
				}
				if ($field->filluid == "3") {
					return $user->get('username');
				}
				if ($field->filluid == 'url') {
					return JUri::getInstance()->toString();
				}
				if ($field->filluid == 'usermail') {
					return $user->get('email');
				}
				preg_match('/^CF(\d+)$/', $field->filluid, $matches);
				if ($matches && !empty($matches[1])) {
					$customfieldid = $matches[1];
					return JHtmlVisforms::getCustomUserFieldValue($customfieldid);
				}
				$userProfile = JUserHelper::getProfile($userId);
				if ((!(empty($userProfile->profile))) && (is_array($userProfile->profile))) {
					if (!(empty($userProfile->profile[$field->filluid]))) {
						return $userProfile->profile[$field->filluid];
					}
				}
			}
		}
		return false;
	}

	protected function setConfigurationDefault() {
		$this->field->configurationDefault = $this->field->attribute_value;
		$task = $this->input->getCmd('task', '');
		if (($task !== 'editdata') && ($task !== 'saveedit')) {
			$urlparams = JFactory::getApplication()->getUserState('com_visforms.urlparams.' . $this->form->context, null);
			if (!empty($urlparams) && (is_array($urlparams)) && (isset($urlparams[$this->field->name]))) {
				$queryValue = $urlparams[$this->field->name];
			}
			//if form was originally called with valid url params, reset to this url params
			$this->field->configurationDefault = (isset($this->field->allowurlparam) && ($this->field->allowurlparam == true) && isset($queryValue)) ? $queryValue : $this->field->attribute_value;
		}
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
		}
	}
}