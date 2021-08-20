<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2020 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/visforms.php';
require_once JPATH_ROOT . '/libraries/visolutions/tcpdf/tcpdf.php';
require_once JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/pdf/visTCPDF.php';

class visFormsPluginHelper extends JPlugin {

	protected $filesToDelete = array();

	//Backend actions manage plugin configuration settings in database

	//add plugin specific fields to form configuration in administration
	public function onVisformsPrepareJForm($form) {
		$app = JFactory::getApplication();
		if ($app->isClient('administrator')) {
			JForm::addFormPath($this->formDir);
			$form->loadFile($this->name, false);
			$form = $this->removeFields($form);
			$data = $this->loadFormData($form);
			$form->bind($data);
		}
		return true;
	}

	// save values of plugin specific fields from form configuartion
	public function onVisformsSaveJFormExtraData($data, $fid, $isNew) {
		$app = JFactory::getApplication();
		if ($app->isClient('administrator')) {
			$data['fid'] = $fid;
			if (isset($data[$this->name . '_params']) && is_array($data[$this->name . '_params'])) {
				$data[$this->name . '_params'] = VisformsHelper::registryStringFromArray($data[$this->name . '_params']);
			}
			$this->saveExtraData($data, $isNew);
		}
		return true;
	}

	//delete values of plugin specific fields from form configuration in administration
	public function onVisformsAfterJFormDelete($context, $table) {
		// Skip plugin if context is wrong
		if ($context != 'com_visforms.visform') {
			return true;
		}
		$app = JFactory::getApplication();
		if ($app->isClient('administrator')) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__' . $this->name))
				->where($db->quoteName('fid') . ' = ' . $table->id);
			$db->setQuery($query);
			$result = $db->execute();
		}
		return true;
	}

	// handle plugin specific options on batch copy of form
	public function onVisformsAfterBatchCopyForm($oldFormId, $newFormId) {
		$app = JFactory::getApplication();
		if ($app->isClient('administrator')) {
			$data = $this->getItem($oldFormId, false);
			if (empty($data)) {
				return true;
			}
			$data['fid'] = $newFormId;
			return $this->saveExtraData($data, true);
		}
		return true;
	}

	protected function loadFormData($form) {
		$data = $this->getItem();
		if (empty($data)) {
			$data = array();
		}
		return $data;
	}

	protected function getItem($fid = null, $extract = true) {
		if (empty($fid)) {
			$fid = $this->fid;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__' . $this->name))
			->where($db->quoteName('fid') . ' = ' . $fid);
		try {
			$db->setQuery($query);
			$data = $db->loadAssoc();
		}
		catch (RuntimeException $e) {
			
		}
		if ($data && $extract) {
			$data[$this->name . '_params'] = VisformsHelper::registryArrayFromString($data[$this->name . '_params']);
		}
		return $data;
	}


	protected function saveExtraData($data, $isNew) {
		JTable::addIncludePath($this->tableDir);
		$table = JTable::getInstance($this->plgTableName, 'Table', $config = array());
		$tableKey = $this->tableKey;
		$app = JFactory::getApplication();
		if (!($isNew)) {
			if (!isset($data[$tableKey])) {
				return false;
			}
			$data[$tableKey] = (int) $data[$tableKey];
			$table->load($data[$tableKey]);
		} else {
			unset($data[$tableKey]);
		}
		if (!$table->bind($data)) {
			$app->enqueueMessage($table->getError(), 'error');
			return false;
		}

		if (!$table->check()) {
			$app->enqueueMessage($table->getError(), 'error');
			return false;
		}

		if (!$table->store()) {
			$app->enqueueMessage($table->getError(), 'error');
			return false;
		}
		return true;
	}

	protected function createHash() {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$rand = openssl_random_pseudo_bytes(16);
			if ($rand === false) {
				// Broken or old system
				$rand = mt_rand();
			}
		}
		else {
			$rand = mt_rand();
		}
		$hashThis = microtime() . $rand;
		if (function_exists('hash')) {
			$hash = hash('sha256', $hashThis);
		}
		else if (function_exists('sha1')) {
			$hash = sha1($hashThis);
		}
		else {
			$hash = md5($hashThis);
		}
		return $hash;
	}

	protected function storeFile($name, $content) {
		JLoader::import('joomla.filesystem.file');
		$ret = JFile::write($this->outputPath . $name, $content);

		if ($ret) {
			// return the name of the file
			return $name;
		}
		else {
			return false;
		}
	}

	protected function deleteFiles() {
		// Delete the PDF data to disk using JFile::delete();
		JLoader::import('joomla.filesystem.file');
		$filesToDelete = $this->filesToDelete;
		foreach ($filesToDelete as $fileToDelete) {
			$path = $this->outputPath;
			$file = JPath::clean($path . $fileToDelete);
			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}
	}

	protected function createPDF($html, $pdfHideHeader = 1, $pdfHideFooter = 1) {
		// Create the PDF
		$pdf = $this->initializeTCPDF();
		if (!(empty($pdfHideHeader))) {
			$pdf->SetPrintHeader(false);
		}
		if (!(empty($pdfHideFooter))) {
			$pdf->SetPrintFooter(false);
		}
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		$pdfData = $pdf->Output('', 'S');

		unset($pdf);
		// create file name
		$hash = $this->getFileName();
		$name = $hash . '.pdf';
		// write file
		return $this->storeFile($name, $pdfData);
	}
	
	protected function getFileName () {
		return $this->createHash();
	}

	protected function initializeTCPDF() {

		$pdf = new visTCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('Visforms');
		$pdf->SetSubject('Visforms');
		$pdf->setShowHeader(0);
		$pdf->setShowFooter(0);

		// check and set font areas
		$pdf->setHeaderFont(array('dejavusans', '', 8, '', false));
		$pdf->setFooterFont(array('dejavusans', '', 8, '', false));
		$pdf->SetFont('dejavusans', '', 10, '', false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(15, 27,15);
		$pdf->SetHeaderMargin(5);
		$pdf->SetFooterMargin(10);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			global $l;
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// set default font sub setting mode
		$pdf->setFontSubsetting(true);

		return $pdf;
	}
	
	protected function removeFields($form) {
		return $form;
	}
}