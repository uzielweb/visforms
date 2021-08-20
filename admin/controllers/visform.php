<?php
/**
 * visdforms controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

class VisformsControllerVisform extends JControllerForm
{
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerTask('fields', 'formParts');
		$this->registerTask('datas', 'formParts');
		$this->registerTask('pdfs', 'formParts');
	}
	
	protected function postSaveHook(JModelLegacy $model, $validData = array()) {
		$item = $model->getItem();
		$id = $item->get('id');
		if ($id) {
			// create a new datatable if it doesn't already exist
			$model->createDataTables($id, $validData['saveresult']);
		}
		$spambotParams = $item->get('spamprotection');
		if (($spambotParams['spbot_log_to_db'])) {
			$model->createSpambotTableIfNotExist();
		}
	}
	
	public function batch($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// set the model
		$model = $this->getModel('Visform', '', array());
		// preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms' . $this->getRedirectToListAppend(), false));
		return parent::batch($model);
	}

	public function formParts() {
		$fid = $this->input->getInt('id', -1);
		$task = $this->input->getCmd('task', 'fields');
		$context = "$this->option.edit.$this->context";
		$this->getModel()->checkin($fid);
		$this->releaseEditId($context, $fid);
		$this->setRedirect( "index.php?option=com_visforms&view=vis$task&fid=".$fid);
		return true;
	}
	
	protected function allowEdit($data = array(), $key = 'id') {
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');
		// check general edit permission first
		if ($user->authorise('core.edit', 'com_visforms.visform.' . $recordId)) {
			return true;
		}

		// fallback on edit.own
		// first test if the permission is available
		if ($user->authorise('core.edit.own', 'com_visforms.visform.' . $recordId)) {
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId) {
				// need to do a lookup from the model
				$record = $this->getModel()->getItem($recordId);
				if (empty($record)) {
					return false;
				}
				$ownerId = $record->created_by;
			}

			// if the owner matches 'me' then do the test
			if ($ownerId == $userId) {
				return true;
			}
		}
		
		return false;
	}

	public function getSortOrderFieldOptions() {
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$id = $this->input->getCmd('fid', 0, 'INT');
		$optionHelper = new visFormsSortOrderHelper($id);
		$options = $optionHelper->getOptions();
		echo JHtml::_('select.options', $options);
		JFactory::getApplication()->close();
	}

	public function importform() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms' . $this->getRedirectToListAppend(), false));
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (!$hasSub || !JFactory::getUser()->authorise('core.create', 'com_visforms')) {
			$app->enqueueMessage(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'), 'error');
			return false;
		}
		$upload   = $app->input->files->get('files');
		// todo do we need to check file for bad content; maybe we skip this here because pdf templates can contain html code?
		/*$allowedExtensions = 'json';
		if (!VisformsHelper::canUpload($upload, $allowedExtensions)) {
			// Can't upload the file
			return false;
		}*/
		if (!isset($upload['type']) || $upload['type'] !== 'application/json' || !isset($upload['tmp_name'])) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_IMPORT_FORM_UPLOAD_FILE_ERROR'), 'error');
			return false;
		}
		$jsonDefinition = @file_get_contents($upload['tmp_name']);
		if (empty($jsonDefinition)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_IMPORT_FORM_UPLOAD_FILE_EMPTY'), 'error');
			return false;
		}
		$datas = json_decode($jsonDefinition, true);
		if (empty($datas)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_IMPORT_FORM_UPLOAD_FILE_DEFINITION_INVALID'), 'error');
			return false;
		}
		$helper = new visFormsImportHelper();
		if ($helper->importForms($datas)) {
			// create forms fields and data recors first
			$app->enqueueMessage(JText::_('COM_VISFORMS_IMPORT_FORM_UPLOAD_FILE_INSTALLED'), 'success');
			return true;
		}
		$app->enqueueMessage(JText::_('COM_VISFORMS_UNABLE_TO_IMPORT_FORM_DEFINITION'), 'error');
		return false;
	}
	
	public function exportform() {
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms' . $this->getRedirectToListAppend(), false));
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (!$hasSub || !JFactory::getUser()->authorise('core.create', 'com_visforms')) {
			$app->enqueueMessage(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'), 'error');
			return false;
		}
		$cid  = $this->input->get->get('cid', array(), 'array');
		$exportOptions = $this->input->get->get('export', array(), 'array');
		$helper = new visformsExportHelper($cid, $exportOptions);
		$helper->exportform();
		$app->enqueueMessage('Unable to export form definition', 'error');
		return true;
	}
}
