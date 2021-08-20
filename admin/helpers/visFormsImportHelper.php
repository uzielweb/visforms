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
require_once JPATH_ADMINISTRATOR . '/components/com_visforms/models/visform.php';
require_once JPATH_ADMINISTRATOR . '/components/com_visforms/models/visfield.php';

class visFormsImportHelper
{
	protected $formKey;
	protected $pdfKey;
	protected $fieldsKey;
	protected $dataKey;
	// map field ids from export source to new fieldis on import source
	protected $fieldIdMapper;
	// map fid from export source to new fid on import source
	protected $formIdMapper;
	protected $fieldPattern = '/(?:.)*?(F\d+)/';
	protected $formTableNamePattern = '/(?:.)*?([a-zA-Z0-9]+_visforms_\d+)/';
	protected $fieldsWithSql = array('selectsql', 'radiosql', 'multicheckboxsql');

	public function __construct($fieldsIdMap = array(), $formIdMap = array()) {
		$this->formKey = visFormsImportExportHelper::$formKey;
		$this->pdfKey = visFormsImportExportHelper::$pdfKey;
		$this->fieldsKey = visFormsImportExportHelper::$fieldsKey;
		$this->dataKey = visFormsImportExportHelper::$dataKey;
		$this->fieldIdMapper = $fieldsIdMap;
		$this->formIdMapper = $formIdMap;
	}

	public function importForms($datas, $forceCreatedByInData = false) {
		require_once JPATH_ADMINISTRATOR . '/components/com_visforms/models/vispdf.php';
		// create forms fields and data records first
		$recordCount = count($datas);
		for ($i = 0; $i < $recordCount; $i++) {
			$fullFormDefintion = $datas[$i];
			if (!isset($fullFormDefintion[$this->formKey])) {
				unset($datas[$i]);
				continue;
			}
			$form = $fullFormDefintion[$this->formKey];
			$oldFid = $form['id'];
			$form ['id'] = 0;
			$formModel = \JModelLegacy::getInstance('Visform', 'VisformsModel');
			$formModel->save($form);
			$newFid = $formModel->getState($formModel->getName() . '.id');
			$this->formIdMapper[$oldFid] = $newFid;
			if (isset($fullFormDefintion[$this->fieldsKey])) {
				$fields = $fullFormDefintion[$this->fieldsKey];
				$fieldsModel = \JModelLegacy::getInstance('Visfield', 'VisformsModel');
				$idMapper = $fieldsModel->importFieldData($fields, $newFid);
				if (is_array($idMapper)) {
					foreach ($idMapper as $key => $value) {
						$this->fieldIdMapper[$key] = $value;
					}
				}
			}
			if ($form['saveresult']) {
				$formModel->createDataTables($newFid);
				// import data into data table
				if (isset($fullFormDefintion[$this->dataKey]) && is_array($idMapper)) {
					$submittedUserInputs = $fullFormDefintion[$this->dataKey];
					$this->saveDemoFormData($newFid, $idMapper, $submittedUserInputs, $forceCreatedByInData);
				}
			}
		}
		// reset array index in case any values have been unset previously
		array_keys($datas);
		// sql statements in fields with sql statements may refer to for ids and field ids of imported forms
		// try to sanitize those ids
		$this->adaptFieldsSqlStatementToNewForm();
		// sql statements in pdfs may refer to formids and field ids of imported forms
		// create pdf records after all forms are created, try to sanitize form and field ids
		foreach ($datas as $fullFormDefintion) {
			if (isset($fullFormDefintion[$this->pdfKey])) {
				$pdfModel = \JModelLegacy::getInstance('Vispdf', 'VisformsModel');
				$pdfs = $fullFormDefintion[$this->pdfKey];
				$oldFid = $fullFormDefintion[$this->formKey]['id'];
				$pdfIdMapper = array();
				foreach ($pdfs as $pdf) {
					$oldPdfId = $pdf['id'];
					$pdf['fid'] = $this->formIdMapper[$pdf['fid']];
					$pdf['id'] = 0;
					$pdf =  $this->preparePdfRecord($pdf);
					$newPdfId = $pdfModel->importPdf($pdf);
					$pdfIdMapper[$oldPdfId] = $newPdfId;
				}
				if (!empty($pdfIdMapper)) {
					$formModel = \JModelLegacy::getInstance('Visform', 'VisformsModel');
					$formModel->updatePdfIdsInFormOptions($this->formIdMapper[$oldFid], $pdfIdMapper);
				}
			}
		}
		return true;
	}

	public function adaptFieldsSqlStatementToNewForm() {
		foreach ($this->fieldIdMapper as $oldFieldId => $newFieldId) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('defaultvalue', 'typefield')))
				->from($db->quoteName('#__visfields'))
				->where('id = ' . $newFieldId);
			try {
				$db->setQuery($query);
				$result = $db->loadObject();
			}
			catch (RuntimeException $e) {
				continue;
			}
			if (!in_array($result->typefield, $this->fieldsWithSql)) {
				continue;
			}

			// extract default value
			$defaultValue = VisformsHelper::registryArrayFromString($result->defaultvalue);
			$sqlOptionName = 'f_'.$result->typefield.'_sql';
			if (!isset($defaultValue[$sqlOptionName])) {
				continue;
			}
			$defaultValue[$sqlOptionName] = $this->adaptSqlStatementToNewForm($defaultValue[$sqlOptionName]);
			VisformsConditionsHelper::saveDefaultValue($newFieldId, VisformsHelper::registryStringFromArray($defaultValue));
		}
	}

	public function preparePdfRecord($record) {
		$formIdMap = $this->formIdMapper;
		$fieldsIdMap = $this->fieldIdMapper;
		// fix visforms form ids and field ids in demo form pdf statements
		if (isset($record['statements'])) {
			$statements = $record['statements'];
			$statements = json_decode($statements, true);
			if (!empty($statements)) {
				$count = count($statements);
				// get each db field name as captured group and the whole string including the db field name
				$fieldPattern = '/(?:.)*?(F\d+)/';
				// get each visforms datatable as captured group and the whole string including the datatable
				$formTableNamePattern = '/(?:.)*?([a-zA-Z0-9]+_visforms_\d+)/';
				for ($i = 0; $i < $count; $i++) {
					$statement = $statements[$i];
					if (isset($statement['type']) && $statement['type'] === 'form' && isset($statement['id']) && isset($statement['sql'])) {
						if (array_key_exists($statement['id'], $formIdMap)) {
							$statement['id'] = $formIdMap[$statement['id']];
						}
						if (preg_match_all($fieldPattern,  $statement['sql'], $sqlParts)) {
							$statement['sql'] = $this->replaceDbFieldNames( $statement['sql'], $sqlParts, $fieldsIdMap);
							unset($sqlParts);
						}
					}
					if (isset($statement['type']) && $statement['type'] === 'free' && isset($statement['sql'])) {
						if (preg_match_all($fieldPattern,  $statement['sql'], $sqlParts)) {
							$statement['sql'] = $this->replaceDbFieldNames( $statement['sql'], $sqlParts, $fieldsIdMap);
							unset($sqlParts);
						}
						if (preg_match_all($formTableNamePattern,  $statement['sql'], $sqlParts)) {
							$statement['sql'] = $this->replaceDataTableName( $statement['sql'], $sqlParts, $formIdMap);
							unset($sqlParts);
						}
					}
					$statements[$i] = $statement;
				}
			}
			$record['statements'] = $statements;
		}
		// fix visforms form ids and field ids in demo form pdf data sql statements
		$dataStatements = $record['data'];
		$dataStatements = json_decode($dataStatements, true);
		if (!empty($dataStatements)) {
			foreach ($dataStatements as $key => $statement) {
				if (isset($statement['sql'])) {
					if (preg_match_all($fieldPattern,  $statement['sql'], $sqlParts)) {
						$statement['sql'] = $this->replaceDbFieldNames( $statement['sql'], $sqlParts, $fieldsIdMap);
						unset($sqlParts);
					}
				}
				$dataStatements[$key] = $statement;
			}
			$record['data'] = $dataStatements;
		}

		return $record;
	}

	protected function adaptSqlStatementToNewForm($sql) {
		if (empty($sql)) {
			return '';
		}
		// get each db field name as captured group and the whole string including the db field name
		if (preg_match_all($this->fieldPattern,  $sql, $sqlParts)) {
			$sql = $this->replaceDbFieldNames( $sql, $sqlParts, $this->fieldIdMapper);
			unset($sqlParts);
		}
		// get each visforms datatable as captured group and the whole string including the datatable
		$sql = JFactory::getDbo()->replacePrefix($sql);
		if (preg_match_all($this->formTableNamePattern,  $sql, $sqlParts)) {
			$sql = $this->replaceDataTableName( $sql, $sqlParts, $this->formIdMapper);
			unset($sqlParts);
		}
		return $sql;
	}

	protected function replaceDbFieldNames($sqlStatement, $sqlParts, $fieldsIdMap) {
		if (empty($sqlParts) || empty($sqlParts[1])) {
			return $sqlStatement;
		}
		$newParts = array();
		foreach ($sqlParts[1] as $key => $field) {
			$oldFieldId = str_replace('F', '', $field);
			if (array_key_exists($oldFieldId, $fieldsIdMap)) {
				$newParts[] = str_replace($field, 'F' . $fieldsIdMap[$oldFieldId], $sqlParts[0][$key]);
			}
			else {
				$newParts[] = $sqlParts[0][$key];
			}
		}
		if (count($newParts) === count($sqlParts[0]) && count($newParts) > 0) {
			$replace = implode('', $newParts);
			$orgText = implode('', $sqlParts[0]);
			return str_replace($orgText, $replace, $sqlStatement);
		}
		return $sqlStatement;
	}

	protected function replaceDataTableName($sqlStatement, $sqlParts, $formIdMap) {
		if (empty($sqlParts) || empty($sqlParts[1])) {
			return $sqlStatement;
		}
		$newParts = array();
		foreach ($sqlParts[1] as $key => $tableName) {
			// explode table name. first part is table prefix, second part is string visforms third part is form id
			$tableNameParts = explode('_', $sqlParts[1][$key]);
			if (array_key_exists($tableNameParts[2], $formIdMap)) {
				$tableNameParts[0] = JFactory::getDbo()->getPrefix();
				$tableNameParts[2] = $formIdMap[$tableNameParts[2]];
				$newTableName = $tableNameParts[0] . $tableNameParts[1] . '_' . $tableNameParts[2];
				$newParts[] = str_replace($tableName, $newTableName, $sqlParts[0][$key]);
			}
			else {
				$newParts[] = $sqlParts[0][$key];
			}
		}
		if (count($newParts) === count($sqlParts[0]) && count($newParts) > 0) {
			$replace = implode('', $newParts);
			$orgText = implode('', $sqlParts[0]);
			return str_replace($orgText, $replace, $sqlStatement);
		}
		return $sqlStatement;
	}

	protected function saveDemoFormData($fid, $fieldIdMapper, $records, $forceCreatedByInData = false) {
		$db = JFactory::getDbo();
		$tableName = '#__visforms_' . $fid;
		$result = true;
		$user = JFactory::getUser();
		foreach ($records as $record) {
			$data = new StdClass();
			$data->id = 0;
			$data->published = $record['published'];
			$data->created = (empty($record['created'])) ? JFactory::getDate()->toSql() : $record['created'];
			$data->created_by = (isset($record['created_by']) && !empty($record['created_by'])) ? $record['created_by'] : ($forceCreatedByInData ? $user->id : 0) ;
			$data->ismfd = 0;
			$data->ipaddress = $record['ipaddress'];

			// heads up! this is a bit fragile: we assume that the order of the field values in the records array match the field order in dbFieldNames
			// otherwise values will end up in the wrong database field!!
			foreach ($fieldIdMapper as $oldId => $newId) {
				$oldId = 'F' . $oldId;
				$newId = 'F' . $newId;
				$data->$newId = $record[$oldId];
			}
			try {
				$db->insertObject($tableName, $data);
			}
			catch (RuntimeException $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				$result = false;
			}
		}
		return $result;
	}
}