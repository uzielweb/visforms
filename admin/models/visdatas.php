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

class VisformsModelVisdatas extends JModelList
{
	public $_data = Array();
	public $unSearchable = array('signature');
	public $unSortable = array('signature');
	protected $_id = null;
	protected $csvHelper;
	
	public function __construct($config = array()) {
        if (!(empty($config['id']))) {
            $id = $config['id'];
        }
        else {
            $id = JFactory::getApplication()->input->getInt('fid', -1);
        }
		$this->setId($id);

		// get an array of fieldnames that can be used to sort data in data table
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'a.ipaddress', 'a.published', 'a.created', 'a.ismfd', 'a.created_by', 'a.modified',
                'id', 'ipaddress', 'published', 'ismfd', 'created_by', 'modified'
			);
		}
		
		// get all form field id's from database
		$db	= JFactory::getDbo();	
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__visfields'))
            ->where($db->quoteName('fid') . " = " . $id);
		$db->setQuery( $query );
		$fields = $db->loadObjectList();
		
		// add field id's to filter_fields
		foreach ($fields as $field) {
			$config['filter_fields'][] = "a.F" . $field->id;
            $config['filter_fields'][] = "F" . $field->id;
		}
		
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) { // Initialise variables.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		// list state information
		parent::populateState('a.id', 'asc');
	}
	
	protected function getStoreId($id = '') {
		// compile the store id
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		return parent::getStoreId($id);
	}
	
	protected function getListQuery() {
		// create a new query object
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$fields = $this->getPublishedDatafields();
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'*'
			)
		);
		$tn = "#__visforms_" . $this->_id;
		$query->from($db->quoteName($tn, 'a'));

		// filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where($db->quoteName('a.published') . ' = ' . (int) $published);
		}
		elseif ($published === '' || is_null($published)) {
            $query->where($db->quoteName('a.published') . ' IN (0,1)');
		}

		// filter by search
		$filter = $this->getFilter();		
		if (!($filter === '')) {
			$query->where($filter);
		}

		// add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'asc');
        // we store dates as strings in database. If sort order field is of type date we have to convert the strings before we order the recordsets
        foreach ($fields as $field) {
            $fName = 'F'.$field->id;
            if (($field->typefield == 'date') && (($orderCol == $fName) || ($orderCol == 'a.' . $fName))) {
                $formats = explode(';', $field->defaultvalue['f_date_format']);
                $format = $formats[1]; 
                $orderCol = ' STR_TO_DATE(' . $orderCol . ', '. $db->quote($format).  ') ';
                break;
            }
	        if ((($field->typefield == 'number') || ($field->typefield == 'calculation')) && (($orderCol == $fName) || ($orderCol == 'a.' . $fName))) {
		        $orderCol = '(' . $orderCol .  ' * 1)';
		        break;
	        }
        }
		$query->order(($orderCol.' '.$orderDirn));
		return $query;
	}
	
	/**
	 * Method to set the form identifier
	 *
	 * @param	int form identifier
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function setId($id) {
		// set id and wipe data
		$this->_id = $id;
	}

	/**
	 * Method to set the text for SQL where statement for search filter
	 *
	 * @return string where statement for SQL
	 * @since	1.6
	 */
	public function getFilter() {
		// get Filter parameters
		$visFilter = $this->getState('filter.search');
		$filter = '';	
		if ($visFilter != '') {
			$filter = $filter." (";
			$fields = $this->getPublishedDatafields();
			$keywords = explode(" ", $visFilter);
			$k=count( $keywords );
			for ($j=0; $j < $k; $j++) {
                $n=count( $fields );
				for ($i=0; $i < $n; $i++) {
                    $rowField = $fields[$i];
					if ($rowField->showFieldInDataView && empty($rowField->unSearchable)) {
                        $prop="F".$rowField->id;
						$filter = $filter." upper(".$prop.") like upper('%".$keywords[$j]."%') or ";
					}
				}
				$filter = $filter." ipaddress like '%".$keywords[$j]."%' or ";
			}
			$filter = rtrim($filter,'or '); 
			$filter = $filter." )";
		}
		return $filter;
	}

	//when we call getDatafields directly from view.html with get() method, we cannot add parameters and get unpublished fields as well
	public function getPublishedDatafields() {
		return $this->getDatafields('published = 1');
	}

	/**
	 * @param string $where
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public function getDatafields($where = "") {
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		// lets load the data if it doesn't already exist
		$query->select('*')
			->from($db->qn('#__visfields'))
			->where($db->qn('fid') . '=' .$this->_id);
        if ($where != '') {
	        $query->where($where);
        }
		$query->order('ordering ASC');
        try {
            $datafields = $this->_getList( $query );

	        foreach($datafields as $dataField) {
	            $dataField->defaultvalue = VisformsHelper::registryArrayFromString($dataField->defaultvalue);
	            if($dataField->typefield == "fieldsep" || $dataField->typefield == "image" || $dataField->typefield == "submit" || $dataField->typefield == "reset") {
	                $dataField->showFieldInDataView = false;
	            }
	            else {
	                $dataField->showFieldInDataView = true;
	            }
		        if (in_array($dataField->typefield, $this->unSortable)) {
			        $dataField->unSortable = true;
		        } else {
			        $dataField->unSortable = false;
		        }
		        if (in_array($dataField->typefield, $this->unSearchable)) {
			        $dataField->unSearchable = true;
		        } else {
			        $dataField->unSearchable = false;
		        }
		        if ($dataField->typefield == "signature") {
			        $dataField->canvasWidth = (isset($dataField->defaultvalue['f_signature_canvasWidth'])) ? $dataField->defaultvalue['f_signature_canvasWidth'] : 280;
			        $dataField->canvasHeight = (isset($dataField->defaultvalue['f_signature_canvasHeight'])) ? $dataField->defaultvalue['f_signature_canvasHeight'] : 120;
		        }
	        }
        }
        catch (RuntimeException $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
		return $datafields;
	}
    
    /**
	 * Method to test whether a record can be exported.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   11.1
	 */
	public function canExport($fid) {
        $user = JFactory::getUser();
		// check form settings
		if ($fid != -1) {
            return $user->authorise('core.export.data', 'com_visforms.visform.' . (int) $fid);
		}
		else {
			// use component settings
            return $user->authorise('core.export.data', 'com_visforms');
        }
    }
    
    public function getFilterForm($data = array(), $loadData = true) {
		$form = parent::getFilterForm($data, $loadData);
		if (empty($form)) {
            return false;
		}
        
        // configure sort list - create two options for each visforms form field (asc and desc) and replace definition of fullordering field in filter_visdatas.xml
        $xml = 
            '<field' . $this->getFilterFullOrderingFieldAttributes() . ' >
			<option value="">JGLOBAL_SORT_BY</option>
            <option value="a.id ASC">JGRID_HEADING_ID_ASC</option>
			<option value="a.id DESC">JGRID_HEADING_ID_DESC</option>
            <option value="a.published ASC">JSTATUS_ASC</option>
			<option value="a.published DESC">JSTATUS_DESC</option>
            <option value="a.created ASC">JDATE_ASC</option>
			<option value="a.created DESC">JDATE_DESC</option>
			<option value="a.ipaddress ASC">COM_VISFORMS_SORT_IP_ASC</option>
			<option value="a.ipaddress DESC">COM_VISFORMS_SORT_IP_DESC</option>
            <option value="a.ismfd ASC">COM_VISFORMS_SORT_ISMFD_ASC</option>
			<option value="a.ismfd DESC">COM_VISFORMS_SORT_ISMFD_DESC</option>
            <option value="a.created_by ASC">COM_VISFORMS_SORT_CREATED_BY_ASC</option>
			<option value="a.created_by DESC">COM_VISFORMS_SORT_CREATED_BY_DESC</option>
			<option value="a.modified ASC">COM_VISFORMS_SORT_MODIFIED_AT_ASC</option>
			<option value="a.modified DESC">COM_VISFORMS_SORT_MODIFIED_AT_DESC</option>
			'
        ;
     
        $datafields = $this->getPublishedDatafields();
        foreach($datafields as $dataField) {
            if(isset($dataField->showFieldInDataView) && $dataField->showFieldInDataView == true && empty($dataField->unSortable)) {
                $xml .= '<option value="a.F' . $dataField->id . ' ASC">' . $dataField->name . ' ' . JText::_("COM_VISFORMS_ASC") . '</option>';
                $xml .= '<option value="a.F' . $dataField->id . ' DESC">' . $dataField->name . ' ' . JText::_("COM_VISFORMS_DESC") . '</option>';
            }
        }

        $xml .= '</field>';
        $xmlField = new SimpleXMLElement($xml);
        $form->setField($xmlField, 'list', 'true');
		return $form;
	}

	protected function getFilterFullOrderingFieldAttributes() {
		return '
			name="fullordering"
			type="list"
			label="COM_VISFORMS_LIST_FULL_ORDERING"
			description="COM_VISFORMS_LIST_FULL_ORDERING_DESC"
			onchange="this.form.submit();"
			default="a.id ASC"';
	}

	// allways set csvHelper first
	public function setCsvHelper($params = null) {
		$this->csvHelper = new visFormCsvHelper($this->_id, $params);
	}
    
    public function createExportBuffer ($cIds = array()) {
	    return $this->csvHelper->createExportBuffer($cIds);
    }

    public function getCsvFileName() {
	    $defaultFileName = "visforms_" . date("Ymd");
	    return $this->csvHelper->getExportFileName($defaultFileName);
    }

    public function getItemsTotal($fid = 0) {
		$this->_id = $fid;
		try {
			return (int) $this->_getListCount($this->getListQuery());
		}
		catch (RuntimeException $e) {
			return 0;
		}
	}
}
