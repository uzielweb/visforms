<?php
/**
 * visdata model for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die( 'Restricted access' );
require_once(JPATH_ROOT . '/administrator/components/com_visforms/models/visdatas.php');

class VisformsModelVisdatasPdf extends VisformsModelVisdatas
{
	private $dataFields;
	private $fArray;
	// returned filter must not be null
	private $filter = '';

	// construction

	public function __construct($config = array()) {
		parent::__construct($config);
		if (!(empty($config['where']))) {
			$this->filter = $config['where'];
		}
	}

	// overwrites

	public function getFilter() {
		// filter might be declared using either filed name or field placeholder ( [NAME] )
		$parsed = $this->substituteFieldPlaceHolders($this->filter);
		return $parsed;
	}

	protected function getFilterFullOrderingFieldAttributes() {
		return '
			name="fullordering"
			type="list"
			label="COM_VISFORMS_LIST_FULL_ORDERING"
			description="COM_VISFORMS_LIST_FULL_ORDERING_DESC"
			onchange=""
			default="a.id ASC"';
	}

	protected function populateState($ordering = null, $direction = null) {
		// prevent parent from setting any state value: wring list and ordering
	}

	// interface

	public function substituteFieldPlaceHolders($text) {
		if(empty($text)) {
			return '';
		}
		$placeholders = new VisformsPlaceholder($text);
		if(empty($this->fArray)) {
			// only once: keep for subsequent function calls
			$this->dataFields = $this->getDatafields();
			$this->fArray = json_decode(json_encode($this->dataFields), true);
		}
		while ($placeholders->hasNext()) {
			$placeholders->getNext();
			$tag = $placeholders->getPlaceholderPart('name');
			$replace = $tag;
			if (is_numeric($fIndex = array_search($tag, array_column($this->fArray, 'name')))) {
				$replace = 'F' . $this->dataFields[$fIndex]->id;
			}
			$placeholders->replace($replace);
		}
		$text = $placeholders->getText();

		return $text;
	}

	public function getFieldPlaceHolders() {
		if(empty($this->fArray)) {
			// only once
			$this->dataFields = $this->getDatafields();
			$this->fArray = json_decode(json_encode($this->dataFields), true);
		}
		return array_column($this->fArray, 'name');
	}

	public function populateFilterState($sort, $published = '', $search = '', $start = '', $limit = '') {
		$values = explode(' ', $sort);
		if(2 == count($values)) {
			$this->setState('list.ordering', $values[0]);
			$this->setState('list.direction', $values[1]);
		}

		if(is_numeric($start) && 0 < $start) {
			// user sees first = 1 which is first = 0 for database
			--$start;
		}
		$this->setState('list.start', $start);
		$this->setState('list.limit', $limit);

		$this->setState('filter.published', $published);
		$this->setState('filter.search', $search);
	}

	public function getPrintableDataFields() {
		$dataFields = $this->getAllDatafields();
		foreach($dataFields as $index => $dataField) {
			if($dataField->typefield == "pagebreak" || $dataField->typefield == "fieldsep" || $dataField->typefield == "image" || $dataField->typefield == "submit" || $dataField->typefield == "reset") {
				unset($dataFields[$index]);
			}
			if (!$dataField->published) {
				unset($dataFields[$index]);
			}
		}
		// todo: unset does not change index in array, renumber?
		return $dataFields;
	}

	public function getSelectableDataFields() {
		$dataFields = $this->getAllDatafields();
		foreach($dataFields as $index => $dataField) {
			if($dataField->typefield == "image" || $dataField->typefield == "submit" || $dataField->typefield == "reset") {
				unset($dataFields[$index]);
			}
		}
		// todo: unset does not change index in array, renumber?
		return $dataFields;
	}

	public function getCreatableDataFields() {
		return $this->getAllDatafields();
	}

	// implementation

	private function getAllDatafields($where = "") {
		// lets load the data if it doesn't already exist
		$query = ' SELECT * from #__visfields as c where c.fid='.$this->_id;
		if ($where != '') {
			$query .= $where;
		}
		$query .= ' ORDER BY c.ordering ASC ';
		return $this->_getList( $query );
	}
}
