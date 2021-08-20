<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2019 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class visFormsExportHelper
{
	protected $formKey;
	protected $pdfKey;
	protected $fieldsKey;
	protected $dataKey;
	protected $cids;
	protected $copyFields;
	protected $copyData;
	protected $copyPDFTemplate;
	protected $copyUserId;
	protected $copyACL;
	protected $copyCreated = false;

	public function __construct($cids, $exportOptions = array()) {
		$this->formKey = visFormsImportExportHelper::$formKey;
		$this->pdfKey = visFormsImportExportHelper::$pdfKey;
		$this->fieldsKey = visFormsImportExportHelper::$fieldsKey;
		$this->dataKey = visFormsImportExportHelper::$dataKey;
		$this->pluginsWithDbTable = visFormsImportExportHelper::$pluginsWithDbTable;
		$this->cids = $cids;
		$this->setExportOptions($exportOptions);
	}

	public function exportform() {
		$db = JFactory::getDbo();
		$app = \JFactory::getApplication();
		$exportData = array();
		foreach ($this->cids as $id) {
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->qn('#__visforms'))
				->where($db->qn('id') . ' = ' . $id);
			try {
				$db->setQuery($query);
				$form = $db->loadAssoc();
			}
			catch (RuntimeException $e) {
				$app->enqueueMessage($e->getMessage(), 'error');
			}
			if (!empty($form)) {
				$exportFormData = array();
				$fid = $form['id'];
				if ($rules = $this->getAcl($form['asset_id'])) {
					$form['rules'] = $rules;
				}
				$form = $this->removeInstallationDependantData($form);
				$saveResult = $form['saveresult'];
				// get subscription plugin data
				$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
				if ($hasSub) {
					foreach ($this->pluginsWithDbTable as $table) {
						$query = $db->getQuery(true);
						$query->select('*')
							->from($db->qn('#__' . $table))
							->where($db->qn('fid') . ' = ' . $fid);
						try {
							$db->setQuery($query);
							$plugin = $db->loadAssoc();
						}
						catch (RuntimeException $e) {
							$app->enqueueMessage($e->getMessage(), 'error');
						}
						if (!empty($plugin)) {
							// some subscription plugins have configuration stored in indivudual tables.
							// Using administration ui, data are part of the form configuration and stored using the onVisformsSaveJFormExtraData event
							// set plugin configuration data as form properties in order to get them stored on import without further code
							foreach ($plugin as $key => $value) {
								$form[$key] = $value;
							}
						}
					}
				}
				$exportFormData[$this->formKey] = $form;
				// pdf, fields and data model require fid in input, we have to set it manually
				$app->input->set('fid', $fid);
				// get fields
				if ($this->copyFields) {
					$query = $db->getQuery(true);
					$query->select('*')
						->from($db->qn('#__visfields'))
						->where($db->qn('fid') . ' = ' . $fid)
						->order($db->qn('ordering') . ' ASC');
					try {
						$db->setQuery($query);
						$fields = $db->loadObjectList();
					}
					catch (RuntimeException $e) {
						$app->enqueueMessage($e->getMessage(), 'error');
					}
					if (!empty($fields)) {
						$exportFormData[$this->fieldsKey] = array();
						foreach ($fields as $field) {
							if ($rules = $this->getAcl($field->asset_id)) {
								$field->rules = $rules;
							}
							$field = $this->removeInstallationDependantData($field);
							$exportFormData['fields'][] = $field;
						}
					}
				}
				if ($this->copyData) {
					if ($saveResult && $includeDataOnExport = true) {
						$query = $db->getQuery(true);
						$query->select('*')
							->from($db->qn('#__visforms_' . $fid));
						try {
							$db->setQuery($query);
							$datas = $db->loadObjectList();
						}
						catch (RuntimeException $e) {
							$app->enqueueMessage($e->getMessage(), 'error');
						}
						if (!empty($datas)) {
							$this->copyCreated = true;
							$exportFormData[$this->dataKey] = array();
							foreach ($datas as $data) {
								$data = $this->removeInstallationDependantData($data);
								$exportFormData['datas'][] = $data;
							}
						}
					}
				}
				if ($this->copyPDFTemplate) {
					$query = $db->getQuery(true);
					$query->select('*')
						->from($db->qn('#__vispdf'))
						->where($db->qn('fid') . ' = ' . $fid);
					try {
						$db->setQuery($query);
						$pdfs = $db->loadObjectList();
					}
					catch (RuntimeException $e) {
						$app->enqueueMessage($e->getMessage(), 'error');
					}
					if (!empty($pdfs)) {
						$this->copyCreated = false;
						$exportFormData[$this->pdfKey] = array();
						foreach ($pdfs as $pdf) {
							if ($rules = $this->getAcl($pdf->asset_id)) {
								$pdf->rules = $rules;
							}
							$pdf = $this->removeInstallationDependantData($pdf);
							$exportFormData['pdfs'][] = $pdf;
						}
					}
				}
			}
			$exportData[] = $exportFormData;
		}
		$exportString = json_encode($exportData);
		if (!empty($exportString)) {
			$name = 'form' . $fid . date('Ymd-His') . '.json';
			header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Content-type: application/json");
			header("Content-disposition: attachment; filename=" . $name);
			echo $exportString;
			$app->close();
		}
		else {
			return false;
		}

		return true;
	}

	protected function removeInstallationDependantData($data) {
		$data = json_decode(json_encode($data),true);
		// set values in form, field, pdf or data record set, which are specific to the current installatiaon, to 0 or null
		// only manipulate parameters, that exist in the current record set
		if (isset($data['hits'])) {
			$data['hits'] = 0;
		}
		if (isset($data['created']) && empty($this->copyCreated)) {
			$data['created'] = null;
		}
		if (isset($data['created_by']) && empty($this->copyUserId)) {
			$data['created_by'] = 0;
		}
		if (isset($data['asset_id'])) {
			$data['asset_id'] = 0;
		}
		if (isset($data['checked_out'])) {
			$data['checked_out'] = null;
		}
		if (isset($data['checked_out_time'])) {
			$data['checked_out_time'] = null;
		}
		if (isset($data['ismfd'])) {
			$data['ismfd'] = 0;
		}
		return $data;
	}

	protected function setExportOptions($exportOptions) {
		$this->copyFields = false;
		$this->copyData = false;
		$this->copyPDFTemplate = false;
		if (empty($exportOptions) && !is_array($exportOptions)) {
			return;
		}
		if (isset($exportOptions['copy-fields']) && $exportOptions['copy-fields'] == 'c') {
			$this->copyFields = true;
		}
		if (isset($exportOptions['copy-data']) && $exportOptions['copy-data'] == 'c' && $this->copyFields ) {
			$this->copyData = true;
		}
		if (isset($exportOptions['copy-pdf-templates']) && $exportOptions['copy-pdf-templates'] == 'c') {
			$this->copyPDFTemplate = true;
		}
		if (isset($exportOptions['copy-userid']) && $exportOptions['copy-userid'] == 'c') {
			$this->copyUserId = true;
		}
		if (isset($exportOptions['copy-acl']) && $exportOptions['copy-acl'] == 'c') {
			$this->copyACL = true;
		}
		return;
	}

	protected function getAcl($assetId) {
		if (empty($this->copyACL) || empty($assetId)) {
			return false;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('rules'))
			->from($db->qn('#__assets'))
			->where($db->qn('id') . ' = ' . $assetId);
		try {
			$db->setQuery($query);
			return $db->loadResult();
		}
		catch (RuntimeException $e) {
			return false;
		}

	}
}