<?php
/**
 * Visforms field textarea class
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


class VisformsFieldTextarea extends VisformsField
{
	//no setting of $this->postValue or $this->editValue in a constructor for this field type. Values are set in $this->getDefaultInputs()
	//no queryValue allowed for textareas!

	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->setIndividualProperties();
		$this->getDefaultInputs();
		$this->setIsConditional();
		$this->setEditValue();
		$this->setConfigurationDefault();
		$this->setEditOnlyFieldDbValue();
		$this->setFieldDefaultValue();
		$this->setDbValue();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->setShowRequiredAsterix();
	}

	protected function setFieldDefaultValue() {
		$field = $this->field;
		if (JFactory::getApplication()->input->getCmd('task', '') == 'editdata') {
			if ((isset($this->field->editValue))) {
				$this->field->initvalue = $this->field->editValue;
			}
			$this->field->dataSource = 'db';
			return;
		}
		//if we have a POST Value, we use this
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			if (isset($_POST[$field->name])) {
				$this->field->initvalue = $this->postValue;
			} 
			else {
				$this->field->initvalue = $this->field->configurationDefault;
			}
			$this->field->dataSource = 'post';
			return;
		}
		//No query (GET) values for textareas
		//Nothing to do
		return;
	}

	protected function setIndividualProperties() {
		$field = $this->field;
		//we have an HTMLEditor and have to check that it is not empty
		if (isset($field->attribute_required) && isset($field->HTMLEditor) && $field->HTMLEditor == '1' && (!(isset($field->attribute_readonly)) || $field->attribute_readonly != "readonly")) {
			$this->field->textareaRequired = true;
		}
		//We have an HTMLEditor
		if (isset($field->HTMLEditor) && $field->HTMLEditor == '1' && (!(isset($field->attribute_readonly)) || ($field->attribute_readonly != "readonly"))) {
			$this->field->hasHTMLEditor = true;
		}
	}

	private function getDefaultInputs() {
		$field = $this->field;
		if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor == true) {
			$this->postValue = $this->input->post->get($field->name, '', 'RAW');
		} else {
			$this->postValue = $this->input->post->get($field->name, '', 'STRING');
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
		return;
	}

	protected function setConfigurationDefault() {
		$task = $this->input->getCmd('task', '');
		$this->field->configurationDefault = $this->field->initvalue;
		//if ($task === 'send')
		if (($task !== 'editdata') && ($task !== 'saveedit')) {
			$urlparams = JFactory::getApplication()->getUserState('com_visforms.urlparams.' . $this->form->context, null);
			if (!empty($urlparams) && (is_array($urlparams)) && (isset($urlparams[$this->field->name]))) {
				$queryValue = $urlparams[$this->field->name];
			}
			//if form was originally called with valid url params, reset to this url params
			$this->field->configurationDefault = (isset($this->field->allowurlparam) && ($this->field->allowurlparam == true) && isset($queryValue)) ? $queryValue : $this->field->initvalue;
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
				if (isset($this->field->hasHTMLEditor) && $this->field->hasHTMLEditor == true) {
					$this->field->editValue = $filter->clean($data->$datafieldname, 'RAW');
				} else {
					$this->field->editValue = $filter->clean($data->$datafieldname, 'STRING');
				}
			}
		}
	}
}