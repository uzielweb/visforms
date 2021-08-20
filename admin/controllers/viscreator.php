<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/models/helpers/createFormData.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/models/helpers/createFieldData.php');
require_once __DIR__ . '/itemcontrollerbase.php';
class VisformsControllerViscreator extends VisformsItemControllerBase
{
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function createForm() {
		if (!$this->checkAjaxSessionToken()) {
			$message = JText::_("COM_VISFORMS_AJAX_INVALID_TOKEN");
			$formID = 0;
			$fieldIDs = array();
			$sidebar = false;
			$success = false;
		}
		else {
			// get the data
			$data  = $this->getAjaxRequestData();
			$fieldIDs = array();
			// shortcuts
			$formTitle = $data->title;
			$formName = $data->name;
			$saveresult = $data->saveresult;
			$allowfedv = $data->allowfedv;
			$ownrecordsonly = $data->ownrecordsonly;
			// start: create form class
			$form = new createFormData();

			// create the form
			$form->createObject();
			// mandatory
			$form->setParameter('name', $formName);
			$form->setParameter('title', $formTitle);
			// missing
			$form->setParameter('access', '1');
			// optional
			$form->setParameter('saveresult', $saveresult);
			$form->setGroupParameter('frontendsettings', 'allowfedv', $allowfedv);
			$form->setGroupParameter('frontendsettings', 'ownrecordsonly', $ownrecordsonly);
			$form->saveObject();
			$formID = $form->getId();
			$field = new createFieldData($formID);
			// create all fields
			foreach ($data->fields as $index => $params) {
				$field->setType($params->type);
				$field->createObject();
				// mandatory
				$field->setParameter('name', $params->name);
				$field->setParameter('label', $params->label);
				$field->setParameter('typefield', $params->type);
				// add one mandatory option
				if ('radio' == $params->type || 'select' == $params->type || 'multicheckbox' == $params->type) {
					// '{"1":{"listitemid":"0","listitemvalue":"value","listitemlabel":"label"}}';
					// '{"1":{"listitemid":"0","listitemvalue":"value1","listitemlabel":"label1"},"2":{"listitemid":"1","listitemvalue":"value2","listitemlabel":"label2"}}';
					// field name triggered specific semantics
					if ('gender' === $params->name || 'sex' === $params->name) {
						$lastId = 2;
						$options = '{"1":{"listitemid":"0","listitemvalue":"0","listitemlabel":"male"},"2":{"listitemid":"1","listitemvalue":"1","listitemlabel":"female"}}';
					}
					else {
						// the last id is also the options count
						$lastId = 3;
						// build multiple options json string
						$options = '{';
						for ($i = 0, $j = 1; $i < $lastId; $i++, $j++) {
							// leading comma after the very first one
							if (0 < $i) {
								$options .= ',';
							}
							$options .= "\"$j\":{\"listitemid\":\"$i\",\"listitemvalue\":\"value$j\",\"listitemlabel\":\"label$j\"}";
						}
						$options .= '}';
					}
					// set option jason string
					$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_list_hidden', $options);
					$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_lastId', $lastId);
				}
				// set mandatory checked value
				if ('checkbox' == $params->type) {
					$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_attribute_value', 'value');
					$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_unchecked_value', '0');
				}
				// set mandatory checked value
				if ('image' == $params->type) {
					$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_attribute_src', 'images/visforms/visforms-logo-63.png');
				}
				// optional
				if ('submit' !== $params->type && 'reset' !== $params->type) {
					$field->setParameter('frontdisplay', $params->fed ? '1' : '0');
					if ($params->required) {
						$field->setGroupParameter('defaultvalue', 'f_' . $params->type . '_attribute_required', '1');
					}
				}
				$field->saveObject();
				$fieldIDs[] = $field->getId();
			}
			$form->postSaveObjectHook();
			// create form id updated sidebar
			VisformsHelper::addSubmenu('viscreator', $formID, $saveresult);
			$sidebar = JHtmlSidebar::render();
			$success = true;
			$message = JText::_('COM_VISFORMS_CREATOR_FORM_CREATED');
		}
		// return success
		$buffer = ob_get_contents();
		ob_clean();
		$response = array('success' => $success, 'fid' => $formID, 'fields' => $fieldIDs, 'sidebar' => $sidebar, 'message' => $message);
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		echo json_encode($response);
		JFactory::getApplication()->close();
	}

	public function createExampleData() {
		// get the data
		$app   = JFactory::getApplication();
		$input = $app->input;

		if (!$this->checkAjaxSessionToken()) {
			$message = JText::_("COM_VISFORMS_AJAX_INVALID_TOKEN");
			$success = false;
		}
		else {
			$data  = $this->getAjaxRequestData();
			// administrator/components/com_visforms/tables/visdata.php: fetches id out of the input structure
			// may be also set by passing query parameter fid instead
			if (!empty($data->fid)) {
				$input->set('fid', $data->fid);
			}
			if (isset($data->count)) {
				$count = $data->count;
			}
			else {
				$count = 1;
			}
			$user = JFactory::getUser();
			// data model via using datas model provides the fields
			$dataModel = JModelLegacy::getInstance('Visdata', 'VisformsModel');
			$fields = $dataModel->getDatafields();
			// used to set value in Visforms date field
			$startDate = new DateTime('2018-01-01 12:00 GMT');
			for ($counter = 1; $counter <= $count; $counter++) {
				$data = array();
				// ignore or set to zero
				$data['id'] = 0;
				// not the first time: starting with first of month
				if (1 < $counter) {
					// date: add 1 day
					$startDate->add(new DateInterval('P1D'));
				}
				// all framework fields
				$data['created'] = JFactory::getDate()->toSql();
				$data['created_by'] = $user->get('id');
				$data['ipaddress'] = '::1';
				$data['published'] = 1;
				$data['checked_out'] = 0;
				//$data['modified']    = $date;
				//$data['modified_by'] = $user->get('id');
				//$data['checked_out_time'] = '';
				// all user defined fields
				foreach ($fields as $index => $field) {
					$name = 'F' . $field->id;
					switch ($field->typefield) {
						// todo: if possible: generate number for post/zip code
						case 'file':
						case 'fieldsep':
						case 'pagebreak':
						case 'image':
						case 'submit':
						case 'reset':
							break;
						case 'number':
							$data[$name] = (string) (20 + $counter);
							break;
						case 'date':
							$data[$name] = $startDate->format('d.m.Y');
							break;
						case 'checkbox':
							// to check set to $field->attribute_value
							// to uncheck set to $field->unchecked_value
							$data[$name] = $field->attribute_value;
							break;
						case 'multicheckbox':
						case 'radio':
						case 'select':
							// circle though all possible values
							$options = json_decode($field->list_hidden, true);
							// counter started with 1 (- 1): in order to start with first field option
							// array index starts with 1 (+ 1)
							$index = (($counter - 1) % count($options)) + 1;
							$data[$name] = $options[$index]['listitemvalue'];
							break;
						case 'url':
							$data[$name] = 'http://www.site' . $counter . '.com';
							break;
						case 'email':
							$data[$name] = 'user@server' . $counter . '.com';
							break;
						default:
							$data[$name] = $field->label . ' value ' . $counter;
							break;
					}
				}
				// libraries/src/MVC/Model/AdminModel.php tries to get primary key value out of the data model state which gets set after data is stored
				$dataModel->setState($dataModel->getName() . '.id', 0);
				if (!$dataModel->save($data)) {
					// todo: add some error handling
				}
			}
			$success = true;
			$message = $count . ' ' . JText::_('COM_VISFORMS_CREATOR_DATA_CREATED');
		}
		// return success
		$buffer = ob_get_contents();
		ob_clean();
		$response = array("success" => $success, 'message' => $message);
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		echo json_encode($response);
		$app->close();
	}
}