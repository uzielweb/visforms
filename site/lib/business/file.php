<?php
/**
 * Visforms field file business class
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

class VisformsBusinessFile extends VisformsBusiness
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
		$this->setFileInfo();
	}

	protected function validatePostValue() {
		//upload fields do not have a post value
		return true;
	}

	public function validateRequired() {
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$app = JFactory::getApplication();

			//check that a value is set if field is required
			if (isset($this->field->attribute_required)) {

				if ((!(isset($this->field->isDisabled))) || ($this->field->isDisabled === false)) {
					//if we are in the data edit modus we assume that a required file upload field has a value and we only have to make sure that a new file is uploaded if the old one is deleted
					$deleteFlagId = $this->field->name . '-filedelete';
					$deleteFlagValue = $app->input->get($deleteFlagId);
					if ((empty($this->field->recordId)) || (!empty($deleteFlagValue))) {
						if ((isset($_FILES[$this->field->name]['name']) === false) || (isset($_FILES[$this->field->name]['name']) && $_FILES[$this->field->name]['name'] == '')) {
							$this->field->isValid = false;
							$error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED_UPLOAD', $this->field->label);
							$this->setErrors($error);
						}
					}
				}
			}
		}
		return $this->field;
	}
	//if we are editing record set, we gather information of the old file and store it with the field, in order that we can display file information in edit form
	//$file->orgFile information is used in layouts files i.e. layouts/visforms/edit/file/control.php of all edit layouts
	//file information is stored as json object of file name and file path in database
	protected function setFileInfo() {
		//only set, if we are in an "edit" kind of view
		if (empty($this->field->recordId)) {
			return;
		}
		$data = $this->form->data;
		$datafieldname = 'F' . $this->field->id;
		$dbValue = $data->$datafieldname;
		if (empty($dbValue)) {
			return;
		}
		$file = VisformsmediaHelper::getFileInfo($dbValue);
		if (!empty($file)) {
			$this->field->orgfile = $file;
		}
		return;
	}
}
