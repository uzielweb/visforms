<?php
/**
 * Visdata controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once JPATH_SITE.'/components/com_visforms/controller.php';
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visform.php';
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visdata.php';
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visdataspdf.php';

use Joomla\Utilities\ArrayHelper;

class VisformsControllerVisformsdata extends VisformsController
{
	protected $fid;
	protected $canDo;
	protected $parentViewReturnUrl;
	protected $pdfData;
	protected $pdfForm;

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerTask('unpublish', 'publish');
		$this->setParentViewReturnUrl();
		$this->setFid();
		$this->canDo = VisformsHelper::getActions($this->fid);
		$this->registerTask('renderPdfList', 'renderPdf');
	}

	// todo could be moved in a shared controller
	protected function setParentViewReturnUrl() {
		$return = $this->input->get('return', '');
		$this->parentViewReturnUrl = (!empty($return)) ? JHtmlVisforms::base64_url_decode($return) : JURI::base();
	}

	public function publish() {
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $fid = $this->fid;
        //VisformsTableVisdata expects the parameter fid
        $this->setInputFid();
        $pk = $this->input->get('cid', null, 'array');
        //this function can be called from different views, return to return url, if a return param is set in input
	    $dataViewMenuItemExists = JHtmlVisforms::checkDataViewMenuItemExists($fid);
        $mysubmenuexists = JHTMLVisforms::checkMySubmissionsMenuItemExists();
        if (!($dataViewMenuItemExists) && !($mysubmenuexists)) {
			$this->setRedirect($this->parentViewReturnUrl, JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            return false;
        }
        $success = false;
        // Make sure the item ids are integers
		ArrayHelper::toInteger($pk);
        $data = array('publish' => 1, 'unpublish' => 0);
        $task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');
        //check for permission
		if ($this->canDo->get('core.edit.data.state')) {
			if (!empty($pk)) {
				$model = $this->getModel('Visdata', 'Visformsmodel');
				try {
					$result = $model->publish($pk, $value);
					if ($value == 1) {
						$this->setMessage(JText::_('COM_VISFORMS_RECORDSET_PUBLISHED'));
					} elseif ($value == 0) {
						$this->setMessage(JText::_('COM_VISFORMS_RECORDSET_UNPUBLISHED'));
					}
					$success = true;
				}
				catch (Exception $e) {
					$this->setMessage($e->getMessage(), 'error');
				}
			} else {
				$success = false;
			}
		} else {
			$this->setMessage(JText::_('COM_VISFORMS_NO_PUBLISH_AUTHOR'), 'error');
			$success = false;
		}
		$this->setRedirect($this->parentViewReturnUrl);
		return $success;
	}

	public function renderPdf() {
		$this->checkToken();
		$task = $this->getTask();
		$context = $this->input->get('context', '', 'cmd');
		$mid = $this->input->get('mid', 0, 'int');
		$dataModel = $this->getModel('Visformsdata', 'VisformsModel', array('context' => $context, 'mid' => $mid));

		if ($task == 'renderPdfList') {
			// trigger the populateState funciton in model
			$dataModel->getState('list.limit');
			// override list start and list limit
			$dataModel->setState('list.limit', 0);
			$dataModel->setState('list.start', 0);
			$this->pdfData = $dataModel->getItems();
		}
		else {
			// $this->pdfData is expected to be an array of stdClass
			$data = $dataModel->getDetail();
			if (!empty($data)) {
				$this->pdfData = array($data);
			}
		}
		$this->removeForbiddenData();
		return $this->renderPdfDocument();
	}

	public function renderPdfFromRequestData() {
		$this->checkToken('get');
		$visformObject = JFactory::getApplication()->getUserState('visforms'. $this->fid . '.pdf.requestdatas', null);
		$this->pdfData = array($this->createDataArrayFromRequestData($visformObject));
		return $this->renderPdfDocument();
	}

	public function exportCsv() {
		$this->checkToken();
		$this->setRedirect($this->parentViewReturnUrl);
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (!$this->canDo->get('core.export.data.frontend') || empty($hasSub)) {
			$this->setMessage(JText::_('COM_VISFORMS_EXPORT_NOT_PERMITTED'), 'error');
			return false;
		}
		$context = $this->input->get('context', '', 'cmd');
		$dataModel = $this->getModel('Visformsdata', 'VisformsModel', array('context' => $context));
		// trigger the populateState funciton in model
		$dataModel->getState('list.limit');
		// override list start and list limit
		$dataModel->setState('list.limit', 0);
		$dataModel->setState('list.start', 0);
		$items = $dataModel->getItems();
		if (empty($items)) {
			$this->setMessage(JText::_('COM_VISFORMS_NO_ITEM_SELECTED'), 'error');
			return false;
		}
		$exportHelper = new visFormCsvHelper($this->fid, null, $items);
		$buffer = $exportHelper->createExportBuffer();
		if (empty($buffer)) {
			return false;
		}
		$defaultFileName = "visforms_" . date("Ymd");
		$fileName = $exportHelper->getExportFileName($defaultFileName);

		header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=" . $fileName);
		echo $buffer;
		JFactory::getApplication()->close();
	}

	protected function getPdfTemplate() {
		$task = $this->getTask();
		$model = $this->getModel('Visform', 'Visformsmodel');
		$form = $model->getItem();
		switch ($task) {
			case 'renderPdfList' :
				return (!empty($form->frontendsettings['listPdfTemplate'])) ? $form->frontendsettings['listPdfTemplate'] : 0;
			case 'renderPdfFromRequestData':
				return (!empty($form->subredirectsettings['pdf_download_link_template'])) ? $form->subredirectsettings['pdf_download_link_template'] : 0;
			default :
				return (!empty($form->frontendsettings['singleRecordPdfTemplate'])) ? $form->frontendsettings['singleRecordPdfTemplate'] : 0;
		}
	}

	protected function setFid() {
		$this->fid = $this->input->get('id', 0, 'int');
	}

	protected function setInputFid() {
		$this->input->set('fid', $this->fid);
	}

	protected function renderPdfDocument() {
		$fid = $this->fid;
		$datas = $this->pdfData;
		if (!empty($datas)) {
			//find correct pdf-template from form configuration
			$pdfTempl = $this->getPdfTemplate();
			$dataFieldsModel    = JModelLegacy::getInstance('VisdatasPdf', 'VisformsModel', array('id' => $this->fid));
			$dataFields = $dataFieldsModel->getDataFields();
			if (!empty($pdfTempl)) {
				$app = JFactory::getApplication();
				$app->setUserState('visforms'. $fid . '.pdf.datas', $datas);
				$app->setUserState('visforms'. $fid . '.pdf.datafields', $dataFields);
				$urlRenderPdf = JRoute::_("index.php?option=com_visforms&view=visformrenderpdf&format=raw&id=$pdfTempl&fid=$this->fid", false);
				$this->setRedirect($urlRenderPdf);
				return true;
			}
			else {
				$this->setMessage(JText::_('COM_VISFORMS_NO_PDF_TEMPLATE_AVAILABLE'), 'error');
			}
		}
		else {
			$this->setMessage(JText::_('COM_VISFORMS_NO_ITEM_SELECTED'), 'error');
		}

		$this->setRedirect($this->parentViewReturnUrl);
		return false;
	}

	// todo make sure we have all nonFieldPlaceholder
	protected function createDataArrayFromRequestData($visform) {
		$pdfRawData = new stdClass();
		$pdfRawData->id = $visform->dataRecordId;
		$pdfRawData->created_by = JFactory::getUser()->id;
		// todo null or now or pass now with request?
		$pdfRawData->created = null;
		foreach ($visform->fields as $field) {
			$fieldName = 'F' . $field->id;
			if (!empty($field->isDisabled)) {
				$pdfRawData->$fieldName = '';
			}
			else {
				$pdfRawData->$fieldName = $field->dbValue;
			}
		}
		return $pdfRawData;
	}

	protected function removeForbiddenData() {
		$datas = $this->pdfData;
		$canDo = $this->canDo;
		//check for permission
		foreach ($datas as $i => $data) {
			$canCreatePdf = $canDo->get('core.create.pdf');
			$canCreateOwnPdf = $canDo->get('core.create.own.pdf');
			$userid = JFactory::getUser()->id;
			if ($canCreatePdf || ($canCreateOwnPdf && $data->created_by == $userid)) {
				continue;
			}
			// remove recordset, which the user cannot export to pdf
			unset($datas[$i]);
		}
		$this->pdfData = array_values($datas);
	}
}
?>
