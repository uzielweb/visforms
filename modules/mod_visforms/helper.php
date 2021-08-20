<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_visforms
 * @copyright      Copyright (C) vi-solutions, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_visforms/models', 'VisformsModel');

class modVisformsHelper
{
	public static function getForm(&$params) {

		$app = JFactory::getApplication();
		$id = $params->get('catid', 0);
		$context = $params->get('context', '');
		// Get an instance of the generic visforms model
		$model = JModelLegacy::getInstance('Visforms', 'VisformsModel', array('ignore_request' => true, 'id' => $id, 'context' => $context));
		$model->addSupportedFieldType('pagebreak');
		$visforms = $model->getForm();
		if (empty($visforms)) {
			return $visforms;
		}
		//check if user access level allows view
		$user = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$access = (isset($visforms->access) && in_array($visforms->access, $groups)) ? true : false;
		if ($access == false) {
			$app->setUserState('com_visforms.' . $visforms->context, null);
			//don't process fields if user cannot view form (avoid "nothing to setup javascript error of data fields)
			return $visforms;
		}
		$canDo = VisformsHelper::getActions($visforms->id);
		if ((!empty($visforms->redirecttoeditview)) && (!empty($visforms->dataEditMenuExists))) {
			if ($canDo->get('core.edit.own.data')) {
				$datas = $model->getRecords();
				if (!empty($datas)) {
					$editIds = array();
					foreach ($datas as $data) {
						if ((is_object($data)) && (!empty($data)) && !empty($data->id) && isset($data->published)) {
							if (!empty($data->published) || ($canDo->get('core.edit.data.state'))) {
								$editIds[] = (int) $data->id;
							}
						}
					}
				}
				if (!empty($editIds)) {
					$app->setUserState('com_visforms.' . $visforms->context, null);
					//push helper variabels into params
					$params->set('editIds', $editIds);
					$params->set('layout', 'editlink');
					return $visforms;
				}
			}
		}

		$fields = $model->getFields();
		$successMessage = $app->getUserState('com_visforms.messages.' . $visforms->context, '');
		JPluginHelper::importPlugin('content');
		$successMessage = (!empty($successMessage)) ? JHtml::_('content.prepare', $successMessage) : $successMessage;
		$app->setUserState('com_visforms.messages.' . $visforms->context, null);
		$app->setUserState('com_visforms.' . $visforms->context . '.fields', null);
		$app->setUserState('com_visforms.' . $visforms->context, null);
		$visforms->fields = $fields;
		$visforms->parentFormId = 'mod-visform' . $visforms->id;
		//Trigger onFormPrepare event
		JPluginHelper::importPlugin('visforms');
		$app->triggerEvent('onVisformsFormPrepare', array('mod_visforms.form', $visforms, $params));
		$nbFields = count($visforms->fields);
		//get some infos to look whether it's neccessary to add Javascript or special HTML-Code or not
		//variables are set to true if they are true for at least one field
		$required = false;
		$upload = false;
		$textareaRequired = false;
		$hasHTMLEditor = false;
		//helper, used to set focus on first visible field default is no focus
		$firstControl = true;
		$setFocus = (!empty($visforms->setfocus)) ? true : false;
		$steps = (!empty($visforms->steps)) ? (int) $visforms->steps : (int) 1;

		for ($i = 0; $i < $nbFields; $i++) {
			$field = $visforms->fields[$i];
			//set the controll variables
			if (isset($field->attribute_required) && ($field->attribute_required == "required")) {
				$required = true;
			}
			if (isset($field->typefield) && $field->typefield == "file") {
				$upload = true;
			}
			if (isset($field->textareaRequired) && $field->textareaRequired === true) {
				//we have some work to do to use Javascript to validate that the textarea has content
				$textareaRequired = true;
			}
			if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor == true) {
				$hasHTMLEditor = true;
			}
		}

		//push helper variabels into params
		$params->set('nbFields', $nbFields);
		$params->set('required', $required);
		$params->set('upload', $upload);
		$params->set('textareaRequired', $textareaRequired);
		$params->set('hasHTMLEditor', $hasHTMLEditor);
		$params->set('firstControl', $firstControl);
		$params->set('setFocus', $setFocus);
		$params->set('steps', $steps);
		$params->set('successMessage', $successMessage);

		$options = JHtmlVisforms::getLayoutOptions($visforms);

		//process form layout
		$olayout = VisformsLayout::getInstance($visforms->formlayout, $options);
		if (is_object($olayout)) {
			//add layout specific css
			$olayout->addCss();
		}

		return $visforms;
	}
}