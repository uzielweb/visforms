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
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visdatas.php';
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visform.php';

class visFormCsvHelper
{
	protected $fid;
	protected $params;
	protected $dataModel;
	protected $formModel;
	protected $separator;
	protected $input_encoding = "UTF-8";
	protected $output_encoding  = "windows-1250//TRANSLIT";
	protected $useWindowsCharacterSet;
	protected $dataItems;
	protected $exportFields;
	protected $row;
	protected $field;
	protected $lineBuffer = array();
	protected $buffer = array();
	protected $exportAll;
	protected $exportedIds = array();

	public function __construct($fid, $params = null, $dataItems = null) {
		$this->fid = $fid;
		$this->dataModel = JModelLegacy::getInstance('Visdatas', 'VisformsModel', array('id' => $this->fid, 'ignore_request' => true));
		$this->dataModel->setState('list.limit', 0);
		$this->formModel = JModelLegacy::getInstance('Visform', 'Visformsmodel');
		$this->setParams($params);
		$this->getDataItems($dataItems);
		$this->getExportFields();
		$test = true;
	}

	protected function convertCharacterSet($text) {
		if (!$this->useWindowsCharacterSet) {
			return $text;
		}
		// iconv returns false when characters cannot be converted i.e. because some files are not installed
		if (!(false === (bool) (iconv($this->input_encoding, $this->output_encoding, $text)))) {
			// convert characters into window characterset for easier use with excel
			return iconv($this->input_encoding, $this->output_encoding, $text);
		}
		return $text;
	}

	// params object can either be passed to constructor (use with plugin mail attachments) or get from form definition
	protected function setParams($params) {
		if (is_object($params) && !empty($params)) {
			$this->params = $params;
		}
		else {
			$form = $this->formModel->getItem($this->fid);
			$params = new stdClass();
			foreach ($form->exportsettings as $name => $value) {
				// make names shorter and set all export settings as properties of form object
				$params->$name = $value;
			}
			$this->params = $params;
		}
		if ((!function_exists('iconv')) || (isset($this->params->usewindowscharset) && ($this->params->usewindowscharset == 0))) {
			$this->useWindowsCharacterSet = false;
		}
		else {
			$this->useWindowsCharacterSet = true;
		}
		$this->separator = (isset($this->params->expseparator)) ? $this->params->expseparator : ";";
	}

	// record for export can either be passed directly to constructor (used with export from frontend) or we get all data submitted by the form
	// the collection might be reduced to specified record set id's in function createExportBuffer
	protected function getDataItems($dataItems) {
		$this->exportAll = false;
		if (is_array($dataItems) && !empty($dataItems)) {
			$this->dataItems = $dataItems;
			// if record set selection is done by the calling code, we always want to export all record sets
			$this->exportAll = true;
			return;
		}
		$this->dataItems = $this->dataModel->getItems();
	}

	protected function getExportFields() {
		// get fields to export from database
		// according to export parameters of field and form
		$where = ' includefieldonexport = 1';
		$where .= (!(empty($this->params->exppublishfieldsonly))) ? ' and published = 1' : '';
		$where .= " and typefield NOT in('submit', 'image', 'reset', 'fieldsep', 'pagebreak', 'signature')";
		$this->exportFields = $this->dataModel->getDatafields($where);
	}

	protected function createExportCell($type = Null, $prop = Null) {
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if($type == 'field') {
			$row = $this->row;
			$prop = $prop;
		}
		else if ($type == 'label') {
			$row = $this->field;
			$prop = (!empty($row->customlabelforcsv) && $hasSub) ? 'customlabelforcsv': $type;
		}
		else {
			$this->lineBuffer[] = "";
		}

		if ((!isset($prop)) || (!is_string($prop))) {
			$this->lineBuffer[] = "";
		}
		$unicode_str_for_Excel = JHtmlVisformsselect::removeNullbyte($row->$prop);
		$unicode_str_for_Excel = $this->convertCharacterSet($unicode_str_for_Excel);
		$unicode_str_for_Excel = str_replace("\"", "\"\"", $unicode_str_for_Excel);

		$this->lineBuffer[] = $this->escapeSeparator($unicode_str_for_Excel);
	}

	protected function createPreFields() {
		$row = $this->row;
		if (!empty($this->params->expfieldid)) {
			$this->lineBuffer[] = $row->id ;
		}
		if (!empty($this->params->expfieldpublished)) {
			$this->lineBuffer[] = $row->published;
		}
		if (!empty($this->params->expfieldcreated)) {
			$this->lineBuffer[] = VisformsHelper::getFormattedServerDateTime($row->created);
		}
		if (!empty($this->params->expfieldcreatedby)) {
			$this->lineBuffer[] = $row->created_by;
		}
	}

	protected function createPostFields() {
		$row = $this->row;
		if (!empty($this->params->expfieldip)) {
			$this->lineBuffer[] = $row->ipaddress;
		}
		if (!empty($this->params->expfieldismfd)) {
			$this->lineBuffer[] = $row->ismfd;
		}
		if (!empty($this->params->expfieldmodifiedat)) {
			$this->lineBuffer[] = VisformsHelper::getFormattedServerDateTime($row->modified);
		}
	}

	protected function escapeSeparator($text) {
		$pos = strpos($text, $this->separator);
		if ($pos === false) {
			return $text;
		}
		else {
			return "\"".$text."\"";
		}
	}

	public function createExportBuffer ($cIds = array()) {
		if (!(is_object($this->params))) {
			return "";
		}
		$params = $this->params;
		// get submitted form dataset
		$items = $this->dataItems;
		$fields = $this->exportFields;
		$nbItems = count($items);
		$nbFields = count($fields);
		$separator = $this->separator;

		// create table headers from field names
		$this->createHeadLine();
		// create data sets from rows
		for ($i=0; $i < $nbItems; $i++) {
			// set cursor on current item
			$this->row = $items[$i];
			// create a shortcut;
			$row = $this->row;
			// clear line buffer
			$this->lineBuffer = array();
			// exclude unpublished data sets according to form settings and "exportAll" setting
			if(!(empty($params->exppublisheddataonly)) && empty($this->exportAll) && !$row->published) {
				continue;
			}
			// some data sets are checked, we export only those
			if(count($cIds) > 0) {
				foreach($cIds as $value) {
					if($row->id == $value) {
						$this->createPreFields ();
						for ($j=0; $j < $nbFields; $j++) {
							$this->field = $fields[$j];
							$prop="F".$this->field->id;
							$this->prepareValueByFieldType();
							$this->createExportCell('field', $prop);
						}
						$this->createPostFields ();
						$line = implode($separator, $this->lineBuffer);
						if ($line !== '') {
							$this->buffer[] = $line . " \n";
						}
						$this->exportedIds[] = $value;
					}
				}
			}
			// no data sets checked, we export all data sets
			else {
				$this->createPreFields ();
				for ($j=0; $j < $nbFields; $j++) {
					$this->field = $fields[$j];
					$prop="F".$this->field->id;
					$this->prepareValueByFieldType();
					$this->createExportCell('field', $prop);
				}
				$this->createPostFields ();
				$line = implode($separator, $this->lineBuffer);
				if ($line !== '') {
					$this->buffer[] = $line . " \n";
				}
				$this->exportedIds[] = $row->id;
			}
		}
		$csv = implode('', $this->buffer);
		if (!empty($csv)) {
			$csv = rtrim($csv, "\n");
		}
		return $csv;
	}

	protected function prepareValueByFieldType() {
		$rowField = $this->field;
		$prop="F".$rowField->id;
		if ($rowField->typefield == "file") {
			//we must decode json data and extract required values
			if (isset($rowField->fileexportformat) && ((int) $rowField->fileexportformat === 1)) {
				$this->row->$prop = JHtml::_('visforms.getUploadFilePath', $this->row->$prop);
			}
			else if (isset($rowField->fileexportformat) && ((int) $rowField->fileexportformat === 2)) {
				$this->row->$prop = JHtml::_('visforms.getUploadFileFullPath', $this->row->$prop);
			}
			else {
				$this->row->$prop = JHtml::_('visforms.getUploadFileName', $this->row->$prop);
			}
		}
		if ($rowField->typefield == 'location') {
			$tmp = VisformsHelper::registryArrayFromString($this->row->$prop);
			$this->row->$prop = implode(', ', $tmp);
		}
		if ($rowField->typefield == 'textarea') {
			$this->row->$prop = JHtmlVisforms::replaceLinebreaks( $this->row->$prop, " ");
		}
	}

	protected function createHeadLine() {
		$params = $this->params;
		$fields = $this->exportFields;
		$this->lineBuffer = array();
		$nbFields = count($fields);
		// create table headers from field names
		// previous default was, that headers were always created
		if ((!(isset($params->includeheadline))) || ((isset($params->includeheadline)) && ($params->includeheadline == 1))) {
			if (!empty($params->expfieldid)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_ID' ));
			}
			if (!empty($params->expfieldpublished)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_PUBLISHED' ));
			}
			if (!empty($params->expfieldcreated)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_CREATED' ));
			}
			if (!empty($params->expfieldcreatedby)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_CREATED_BY' ));
			}

			for ($i=0; $i < $nbFields; $i++) {
				$this->field = $fields[$i];
				$this->createExportCell('label');
			}
			if (!empty($params->expfieldip)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_IP' ));
			}
			if (!empty($params->expfieldismfd)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_MODIFIED' ));
			}
			if (!empty($params->expfieldmodifiedat)) {
				$this->lineBuffer[] = $this->convertCharacterSet(JText::_( 'COM_VISFORMS_CSV_LABEL_MODIFIED_AT' ));
			}
			$line = implode($this->separator, $this->lineBuffer);
			if ($line !== '') {
				$this->buffer[] = $line  . " \n";
			}
		}
	}

	public function getExportFileName($fileName) {
		$params = $this->params;
		jimport('joomla.filesystem.file');
		if (!empty($params->expfilename)) {
			$customFileName = JFile::stripExt($params->expfilename);
			// use of field placeholder in file name makes only sense, if we only export one record set
			// to fix the problem, when multiple record sets are exported, we always use the value of the last exported record set
			if (!empty($this->exportedIds)) {
				$customFileName = $this->replacePlaceholder($customFileName);
			}
			$fileName = (!empty($params->expfilenameappend)) ? $fileName . $customFileName : $customFileName;
		}

		$fileName = JFile::makeSafe($fileName);
		return $fileName . '.csv';
	}

	protected function replacePlaceholder($text) {
		if (empty($this->exportedIds)) {
			return $text;
		}
		if (empty($this->dataItems)) {
			return $text;
		}
		$cid = array_pop($this->exportedIds);
		foreach ($this->dataItems as $item) {
			if ((int) $item->id === (int) $cid) {
				$record = $item;
				break;
			}
		}
		if (empty($record)) {
			return $text;
		}
		$dArray = json_decode(json_encode($item), true);
		$fields = json_decode(json_encode($this->exportFields));
		if (!empty($fields)) {
			$n = count($fields);
			for ($i = 0; $i < $n; $i++) {
				foreach ($fields[$i]->defaultvalue as $name => $value) {
					// make names shorter and set all defaultvalues as properties of field object
					// used in replacePlaceholder functions
					$prefix = 'f_' . $fields[$i]->typefield . '_';
					if (strpos($name, $prefix) !== false) {
						$key = str_replace($prefix, "", $name);
						$fields[$i]->$key = $value;
					}
				}
			}
		}
		$placeholders = new VisformsPlaceholder($text);
		while ($placeholders->hasNext()) {
			$placeholders->getNext();
			$replace = '';
			$pName = $placeholders->getPlaceholderPart('name');
			if (empty($pName)) {
				// should never happen: just remove the placeholderstring
				$placeholders->replace('');
				continue;
			}
			else if ($placeholders->isNonFieldPlaceholder()) {
				// overhead placeholder
				switch ($pName) {
					case 'id' :
						$replace = (!empty($dArray['id'])) ? $dArray['id'] : '';
						break;
					case 'formtitle' :
						// not supported
						// $replace = (!empty($form->title)) ? $form->title : '';
						break;
					case  'currentdate' :
						$placeholder = VisformsPlaceholderEntry::getInstance('', null, $pName);
						$replace = $placeholder->getReplaceValue();
						break;
					default :
						$placeholder = VisformsPlaceholderEntry::getInstance('', $dArray[$pName], $pName);
						$replace = $placeholder->getReplaceValue();
						break;
				}
			}
			else if (is_array($fields)) {
				foreach ($fields as $field) {
					$fieldName = $field->name;
					if ($pName === $fieldName) {
						$pParams = $placeholders->getPlaceholderPart('params');
						$dataName = 'F' . $field->id;
						$placeholder = VisformsPlaceholderEntry::getInstance($pParams, $dArray[$dataName], $field->typefield, $field);
						$replace = $placeholder->getReplaceValue();
					}
				}
			}

				$placeholders->replace($replace);

			unset($replace);
		}
		return $placeholders->getText();
	}
}