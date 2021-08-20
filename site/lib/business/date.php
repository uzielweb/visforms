<?php
/**
 * Visforms field date business class
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

class VisformsBusinessDate extends VisformsBusinessText
{
	protected function validatePostValue() {
		//update $this->field with value from $this->fields
		$this->updateField();
		$valid = true;
		//only to perform when the value is not empty
		if ($this->field->attribute_value != "") {
			if ((isset($this->field->mindate)) && ($this->field->mindate != '') && isset($this->field->dateFormatPhp)) {
				if (VisformsValidate::validate('mindate', array('date' => $this->field->attribute_value, 'mindate' => $this->field->mindate, 'format' => $this->field->dateFormatPhp, 'mindateformat' => $this->field->dateFormatPhp)) == false) {
					//invalid value
					$valid = false;
					$error = JText::sprintf('COM_VISFORMS_MINDATE_VALIDATION_FAILED', $this->field->label, $this->field->mindate);
					//attach error to form
					$this->setErrors($error);
				}
			} else if (isset($this->field->minvalidation_type) && (strpos($this->field->minvalidation_type, '#field') !== false)) {
				//even if the min date comes from another field, it is not necessary to check if the value in that field is correct,
				//because, if it were invalide, we could only use it anyway and we could just not validate the value in this field then either
				$id = str_replace('#field', '', $this->field->minvalidation_type);
				//get the value of this field
				foreach ($this->fields as $field) {
					if ($field->id == $id) {
						$minDate = $field->attribute_value;
						$minDateFormat = $field->dateFormatPhp;
						break;
					}
				}
				//calucate the min date from value and shift
				if (!empty($minDate)) {
					$min = empty($this->field->dynamic_min_shift) ? $minDate : $minDate . ' ' . $this->field->dynamic_min_shift . ' days';
					$minDate = JHtml::_('date', $min, $minDateFormat);
					if (VisformsValidate::validate('mindate', array('date' => $this->field->attribute_value, 'mindate' => $minDate, 'format' => $this->field->dateFormatPhp, 'mindateformat' => $minDateFormat)) == false) {
						//invalid value
						$valid = false;
						$error = JText::sprintf('COM_VISFORMS_MINDATE_VALIDATION_FAILED', $this->field->label, JHtml::_('date', $min, $this->field->dateFormatPhp));
						//attach error to form
						$this->setErrors($error);
					}
				}

			}
			if ((isset($this->field->maxdate)) && ($this->field->maxdate != '') && isset($this->field->dateFormatPhp)) {
				if (VisformsValidate::validate('maxdate', array('date' => $this->field->attribute_value, 'maxdate' => $this->field->maxdate, 'format' => $this->field->dateFormatPhp, 'maxdateformat' => $this->field->dateFormatPhp)) == false) {
					//invalid value
					$valid = false;
					$error = JText::sprintf('COM_VISFORMS_MAXDATE_VALIDATION_FAILED', $this->field->label, $this->field->maxdate);
					//attach error to form
					$this->setErrors($error);
				}
			} else if (isset($this->field->maxvalidation_type) && (strpos($this->field->maxvalidation_type, '#field') !== false)) {
				//even if the max date comes from another field, it is not necessary to check if the value in that field is correct,
				//because, if it were invalide, we could only use it anyway and we could just not validate the value in this field then either
				$id = str_replace('#field', '', $this->field->maxvalidation_type);
				//get the value of this field
				foreach ($this->fields as $field) {
					if ($field->id == $id) {
						$maxDate = $field->attribute_value;
						$maxDateFormat = $field->dateFormatPhp;
						break;
					}
				}
				//calucate the max date from value and shift
				if (!empty($maxDate)) {
					$max = empty($this->field->dynamic_max_shift) ? $maxDate : $maxDate . ' + ' . $this->field->dynamic_max_shift . ' days';
					$maxDate = JHtml::_('date', $max, $maxDateFormat);
					if (VisformsValidate::validate('maxdate', array('date' => $this->field->attribute_value, 'maxdate' => $maxDate, 'format' => $this->field->dateFormatPhp, 'maxdateformat' => $maxDateFormat)) == false) {
						//invalid value
						$valid = false;
						$error = JText::sprintf('COM_VISFORMS_MAXDATE_VALIDATION_FAILED', $this->field->label, JHtml::_('date', $max, $this->field->dateFormatPhp));
						//attach error to form
						$this->setErrors($error);
					}
				}

			}
			//perform equalTo validation
			if ((isset($this->field->validate_equalTo)) && ($this->field->validate_equalTo != '0')) {
				$value = $this->field->attribute_value;
				$id = str_replace("#field", "", $this->field->validate_equalTo);

				foreach ($this->fields as $equalToField) {
					if ($equalToField->id == $id) {
						if (VisformsValidate::validate('equalto', array('value' => $value, 'cvalue' => $equalToField->attribute_value)) == false) {
							//invalid value
							$valid = false;
							//attach error to form
							$error = JText::sprintf('COM_VISFORMS_EQUAL_TO_VALIDATION_FAILED', $equalToField->label, $this->field->label);
							$this->setErrors($error);
							break;
						}
					}
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
}