<?php
/**
 * Visforms field checkbox class
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

class VisformsFieldCheckbox extends VisformsField
{
	public function __construct($field, $form) {
		parent::__construct($field, $form);
		//store potentiall query Values for this field in the session
		$this->setQueryValue();
		//unchecked checkboxes are not send with post
		//checkboxes always have a value different from empty string (enforced in field configuration)
		//we set unchecked chechboxes in postValue with empty string
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
		$this->setEnterKeyAction();
		$this->setShowRequiredAsterix();
	}

	protected function setFieldDefaultValue() {
		//we never change the attribute value of a checkbox, because this is a fixed value, defined in the field configuration
		//we always set/unset the attribute_checked!
		$field = $this->field;
		if ($this->input->getCmd('task', '') == 'editdata') {
			if (isset($this->field->editValue)) {
				if ($this->field->editValue !== '') {
					$this->field->attribute_checked = "checked";

				}
				else {
					//checkbox is not checked
					if (property_exists($this->field, 'attribute_checked')) {
						unset($this->field->attribute_checked);
					}
				}
			}
			//else use configuration defaults
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			$valid = $this->validateUserInput('postValue');
			//Checkbox was checked, is submitted value correct?
			if ((isset($_POST[$field->name]))) {
				if ($valid === true) {
					if ($this->postValue !== "") {
						$this->field->attribute_checked = "checked";
					}
					else {
						//actually when a $_POST is set, "" is not a valid value therefore we uncheck the box (just as with an invalide value)
						if (property_exists($this->field, 'attribute_checked')) {
							unset($this->field->attribute_checked);
						}
					}
				}
				else {
					if (property_exists($this->field, 'attribute_checked')) {
						unset($this->field->attribute_checked);
					}
				}
			}
			else {
				//checkbox was not submitted with the post (either disabled field or really not checked),
				//we uncheck the field
				//this is necessary because checkboxes can trigger conditional fields if they are checked
				//but in both cases (disabled or really unchecked) the checkbox must not trigger, the display of a depending conditional field
				if (property_exists($this->field, 'attribute_checked')) {
					unset($this->field->attribute_checked);
				}
			}
			//keep configuration default values
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
				//only use a query value if it exists; This value is already validate!
				if ($queryValue === "") {
					//checkbox is not checked
					if (property_exists($this->field, 'attribute_checked')) {
						unset($this->field->attribute_checked);
					}
				}
				else {
					$this->field->attribute_checked = "checked";
				}
			}

			$this->field->dataSource = 'query';
			return;
		}
		//Nothing to do
		return;
	}

	protected function setDbValue() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->field->dbValue = $this->postValue;
		}
	}

	protected function setEditOnlyFieldDbValue() {
		if ($this->field->configurationDefault === "checked") {
			$this->field->editOnlyFieldDbValue = $this->field->attribute_value;
		}
	}

	protected function validateUserInput($inputType) {
		//value set by user
		$value = $this->$inputType;

		//Some empty values are valid but 0 is not
		if ((!isset($value)) || ($value === '')) {
			return true;
		}

		//is there a value set by user which is not allowed?
		if ($value !== $this->field->attribute_value) {
			//we have an invalid user input
			$this->field->isValid = false;
			//attach error to form
			$error = JText::sprintf('COM_VISFORMS_OPTION_HAS_INVALID_POST_VALUE', $this->field->label);
			$this->setErrorMessageInForm($error);
			//remove value from $this->$inputType
			$this->$inputType = "";
			return false;
		}

		return true;
	}

	protected function setRedirectParam() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post' && (!empty($this->field->addtoredirecturl))) {
			// checkbox was not checked
			if (empty($this->postValue)) {
				return;
			}
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
		//empty string is a valid value (= field value is not set)
		if (($queryValue !== '')) {
			if ($queryValue !== $this->field->attribute_value) {
				//remove invalid queryValue ulrparams array and set urlparams to Null if the array is empty
				unset($urlparams[$this->field->name]);
				if (!(count($urlparams) > 0)) {
					$urlparams = null;
				}
				$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
			}
		}
	}

	protected function setConfigurationDefault() {
		$task = $this->input->getCmd('task', '');
		$this->field->configurationDefault = isset($this->field->attribute_checked) ? (string) "checked" : (string) "";
		//if ($task === 'send')
		if (($task !== 'editdata') && ($task !== 'saveedit')) {
			$urlparams = JFactory::getApplication()->getUserState('com_visforms.urlparams.' . $this->form->context, null);
			if (!empty($urlparams) && (is_array($urlparams)) && (isset($urlparams[$this->field->name]))) {
				$queryValue = $urlparams[$this->field->name];
			}
			//if form was originally called with valid url params, reset to this url params
			if (isset($this->field->allowurlparam) && ($this->field->allowurlparam == true) && isset($queryValue)) {
				$this->field->configurationDefault = ($queryValue !== "") ? (string) "checked" : (string) "";
			}
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
			$this->field->editValueChecked = ($this->field->editValue === $this->field->attribute_value) ? (string) "checked" : (string) "";
		}
	}
}