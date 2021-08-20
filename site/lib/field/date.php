<?php
/**
 * Visforms field date class
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

class VisformsFieldDate extends VisformsFieldText
{

	public function __construct($field, $form) {
		parent::__construct($field, $form);
		//store potentiall query Values for this field in the session
		$this->setQueryValue();
		$this->postValue = $this->input->post->get($field->name, '', 'STRING');
		//todo check if other locals have other empty values??
		if ($this->postValue === '0000-00-00 00:00:00') {
			$this->postValue = '';
		}
	}
	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->cleanFieldProperties();
		$this->setIsConditional();
		$this->addDateFormatsToField();
		$this->setMinDate();
		$this->setMaxDate();
		$fillWith = $this->fillWith();
		if ($fillWith !== false) {
			//if we have a special default value set in field declaration we use this
			$this->field->attribute_value = $fillWith;
		}
		//just to make sure that we do not store an invalid attribute value from field configuration as reset value, because invalid date values break the form!
		$this->setInvalidAttributeValueToEmpty();
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
		$this->setShowRequiredAsterix();
	}

	protected function setFieldDefaultValue() {
		$field = $this->field;

		if ($this->input->getCmd('task', '') == 'editdata') {
			//edit value may not set (= NULL) when field was disabled (previous Visforms versions), field was unpublished or field was not created when user inputs were stored
			//we cannot use invalide date values and just display them, because they cause a fatal error on form display
			if (isset($this->field->editValue)) {
				//invalid date values break the form, so check for valid value
				if (VisformsValidate::validate($this->type, array('value' => $this->field->editValue, 'format' => $this->field->dateFormatPhp))) {
					$this->field->attribute_value = $this->field->editValue;
				}
			}
			//else use field configuration default value
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			//this will create a error message on form display when user input is invalid
			$valid = $this->validateUserInput('postValue');
			//$_POST is not set if field was disabled when form was submitted
			//we cannot use invalide date values and just display them, because they cause a fatal error on form display
			if (isset($_POST[$field->name]) && ($valid === true)) {
				$this->field->attribute_value = $this->postValue;
			} //use default values
			else {
				$this->field->attribute_value = $this->field->configurationDefault;
			}
			//else don't change the configuration default value
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
		//Some empty values are valid but 0 is not
		if ((!isset($value)) || ($value === '')) {
			return true;
		}
		//if a value is set we test it has a valid date format
		if (VisformsValidate::validate($type, array('value' => $value, 'format' => $this->field->dateFormatPhp))) {
			return true;
		} 
		else {
			//invalid date format - set field->isValid to false
			$this->field->isValid = false;
			//get the Error Message
			$error = VisformsMessage::getMessage($this->field->label, $type, array('format' => $this->field->dateFormatPhp));
			$this->setErrorMessageInForm($error);
			//we cannot use the value!
			return false;
		}
	}

	private function addDateFormatsToField() {
		$this->field->dateFormatPhp = '';
		$this->field->dateFormatJs = '';
		if (isset($this->field->format)) {
			// get dateformat for php and for javascript
			$dformat = explode(";", $this->field->format);
			if (count($dformat) == 2) {
				$this->field->dateFormatPhp = $dformat[0];
				$this->field->dateFormatJs = $dformat[1];
			}
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
			$valid = VisformsValidate::validate($type, array('value' => $queryValue, 'format' => $this->field->dateFormatPhp));
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

	protected function setInvalidAttributeValueToEmpty() {
		if (VisformsValidate::validate($this->type, array('value' => $this->field->attribute_value, 'format' => $this->field->dateFormatPhp)) === false) {
			$this->field->attribute_value = "";
		}
	}

	protected function fillWith() {
        $field = $this->field;
        //if we have a special default value set in field declaration we use this
        if (strcmp($field->attribute_value, '') == 0 && (isset($field->daydate) && strcmp($field->daydate, '1') == 0)) {
        	$date = empty($field->daydate_shift) ? 'now' : $field->daydate_shift .' days';
        	return JHtml::_('date', $date, $field->dateFormatPhp);
        }
        return false;
    }

	protected function setMinDate()
	{
		if (isset($this->field->minvalidation_type) && !empty($this->field->minvalidation_type)) {
			switch ($this->field->minvalidation_type) {
				case "fix" :
					//use original value of $this->field->mindate
					break;
				case "1" :
					// use (shifted) current date
					$min = empty($this->field->dynamic_min_shift) ? 'now' : $this->field->dynamic_min_shift .' days';
					$this->field->mindate = JHtml::_('date', $min, $this->field->dateFormatPhp);
					break;
				default:
					break;
			}
		} else {
			$this->field->mindate = "";
		}
	}

	protected function setMaxDate()
	{
		if (isset($this->field->maxvalidation_type) && !empty($this->field->maxvalidation_type)) {
			switch ($this->field->maxvalidation_type) {
				case "fix" :
					//use original value of $this->field->maxdate
					break;
				case "1" :
					// use (shifted) current date
					$max = empty($this->field->dynamic_max_shift) ? 'now' : $this->field->dynamic_max_shift .' days';
					$this->field->maxdate = JHtml::_('date', $max, $this->field->dateFormatPhp);
				default:
					break;
			}
		} else {
			$this->field->maxdate = "";
		}
	}

	protected function cleanFieldProperties() {
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (empty($hasSub)) {
			unset($this->field->mindate);
			unset($this->field->maxdate);
			unset($this->field->daydate_shift);
			unset($this->field->dynamic_min_shift);
			unset($this->field->dynamic_max_shift);
			if (isset($this->field->minvalidation_type) && (strpos($this->field->minvalidation_type, '#field') !== false)) {
				$this->field->minvalidation_type = '';
			}
			if (isset($this->field->maxvalidation_type) && (strpos($this->field->maxvalidation_type, '#field') !== false)) {
				$this->field->maxvalidation_type = '';
			}
		}
	}
}