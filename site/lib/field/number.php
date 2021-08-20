<?php
/**
 * Visforms field number class
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
require_once(__DIR__ . '/text.php');

class VisformsFieldNumber extends VisformsFieldText
{

	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->setIsConditional();
		$this->removeInvalidQueryValues();
		$this->setEditValue();
		$this->setConfigurationDefault();
		$this->setEditOnlyFieldDbValue();
		$this->setFieldDefaultValue();
		$this->setDbValue();
		$this->setRedirectParam();
		$this->escapeCustomRegex();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->mendInvalidUncheckedValue();
		$this->setEnterKeyAction();
		$this->setShowRequiredAsterix();
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
			//$_POST is not set if field was disabled when form was submitted
			if (isset($_POST[$field->name])) {
				//use post value for further validation and if form is not valide as re-display value
				$this->field->attribute_value = $this->postValue;
			} //set to empty, if the field is disabled and the form is re-displayed it will be reset to the default after business logic is performed
			else {
				$this->field->attribute_value = $this->field->configurationDefault;
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
		//empty values is valid
		if (empty($value)) {
			return true;
		}
		//if a value is set we test it is a valid number (which still may have dots and commas
		if (VisformsValidate::validate($type, array('value' => $value))) {
			return;
		} else {
			//invalid user inputs - set field->isValid to false
			$this->field->isValid = false;
			//set the Error Message
			$error = VisformsMessage::getMessage($this->field->label, $type);
			$this->setErrorMessageInForm($error);
			return;
		}
	}

	protected function removeInvalidQueryValues() {
		$type = $this->type;
		$app = JFactory::getApplication();
		$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->form->context);
		if (empty($urlparams) || !is_array($urlparams) || !isset($urlparams[$this->field->name])) {
			return;
		}
		$queryValue = $urlparams[$this->field->name];
		//empty string is a valid value (= field value is not set)
		if (($queryValue !== '')) {
			$valid = VisformsValidate::validate($type, array('value' => $queryValue));
			if (empty($valid)) {
				//remove invalid queryValue ulrparams array and set urlparams to Null if the array is empty
				unset($urlparams[$this->field->name]);
				if (!(count($urlparams) > 0)) {
					$urlparams = null;
				}
				$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
			}
		}
	}
}