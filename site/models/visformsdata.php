<?php
/**
 * Visformsdata model for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

//Legacy code for older versions of content plugin vfdataview
if (!class_exists('VisformsHelper')) {
	JLoader::register('VisformsHelper', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/visforms.php');
}
if (!class_exists('VisformsAEF')) {
	JLoader::register('VisformsAEF', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php');
}
if (!class_exists('JHtmlVisformsselect')) {
	JLoader::register('JHtmlVisformsselect', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformsselect.php');
}

class VisformsModelVisformsdata extends JModelList
{
	protected $datafields;
	protected $id;
	protected $detail;
	public $pparams;
	//Dot free string to use as requestprefix and context for pagination, searchfilter and sort fields
	public $paginationcontext;
	public $displayedDefaultDbFields = array();
	public $visform;
	public $pluginfieldlist;
	protected $radius = array('km' => 6371, 'usmiles' => 3959);
	protected $unSortable = array('signature');
	protected $fieldOrder;

	public function __construct($config = array()) {
		if (!empty($config['formid'])) {
			$id = $config['formid'];
		} 
		else {
			$id = JFactory::getApplication()->input->getInt('id', -1);
		}
		$this->setId($id);
		if (isset($config['context']) && $config['context'] != "") {
			$this->context = $config['context'];
		}
		parent::__construct($config);

		// create/ues a unique context which is used to distinguish mulitple adminForms on one page
		$itemid = $this->getMenuId();
		if (!empty($config['mid'])) {
			$itemid = $config['mid'];
		}

		$this->paginationcontext = str_replace('.', '_', $this->context . '_' . $itemid . '_' . $id . '_');

		if (isset($config['pparams']) && is_array($config['pparams'])) {
			$this->pparams = $config['pparams'];
		}

		// get an array of fieldnames that can be used as search filter fields
		// basically we could list all possible filter fields for the form but we keep it cleam
		// which fields are actually used as filters is set in getFilterForm()
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array();
		}
		if (isset($this->pparams) && is_array($this->pparams)) {
			$params = new JRegistry;
			$params->loadArray($this->pparams);
		} 
		else {
			$params = JFactory::getApplication()->getParams();
		}
		$this->fieldOrder = $params->get('fieldorder', 'ordering');
		$this->datafields = $this->getDatafields();
		$fields = $this->datafields;
		if (!empty($fields)) {
			// add field id's to filter_fields
			foreach ($fields as $field) {
				if (in_array($field->typefield, array('select', 'radio', 'multicheckbox', 'checkbox', 'selectsql', 'radiosql', 'multicheckboxsql')) && !empty($field->isfilterfield)) {
					$config['filter_fields'][] = $this->paginationcontext . $field->name;
				}
				if ($field->typefield === 'location') {
					$config['filter_fields'][] = $this->paginationcontext . $field->name;
					$config['filter_fields'][] = $this->paginationcontext . $field->name . '_radius';
				}
				if ($field->typefield === 'date' && !empty($field->isfilterfield)) {
					$config['filter_fields'][] = $this->paginationcontext . $field->name . '_min';
					$config['filter_fields'][] = $this->paginationcontext . $field->name . '_max';
				}
			}
		}
		if ($canPublish = $this->canPublish()) {
			$config['filter_fields'][] = $this->paginationcontext . 'published';
		}
		if ($params->get('show_filter_created')) {
			$config['filter_fields'][] = $this->paginationcontext . 'mincreated';
			$config['filter_fields'][] = $this->paginationcontext . 'maxcreated';
		}

		$this->visform = $this->getForm();
		$this->displayedDefaultDbFields = $this->setDisplayedDefaultDbFields();
		if (array_key_exists('ismfd', $this->displayedDefaultDbFields)) {
			$config['filter_fields'][] = $this->paginationcontext . 'ismfd';
		}
		$this->pluginfieldlist = $this->setPluginFieldList();
		if (isset($config['filter_fields'])) {
			$this->filter_fields = $config['filter_fields'];
		}
	}
	// must stay public
	public function setId($id) {
		// Set id and wipe data
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	protected function populateState($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$itemid = 0;
		if (isset($this->pparams) && is_array($this->pparams)) {
			$params = new JRegistry;
			$params->loadArray($this->pparams);
		} 
		else {
			$params = $app->getParams();
			if ($menu = $this->getMenuItem()) {
				$itemid = ($menu->id) ? $menu->id : 0;
			}
		}
		$this->setState('params', $params);
		$this->setState('itemid', $itemid);
		// Param count comes from plugin, if we have a list view with a limited fix amount of recordsets
		$count = $params->get('count');
		$limit = (isset($count) && is_numeric($count)) ? intval($count) : $params->get('display_num', 20);
		$value = $app->input->get($this->paginationcontext . 'limit', $limit, 'uint');
		$this->setState('list.limit', $value);
		$value = $app->getUserStateFromRequest($this->paginationcontext . '.limitstart', $this->paginationcontext . 'limitstart', 0, 'uint');
		$app->setUserState($this->paginationcontext . '.limitstart', $value);
		$this->setState('list.start', $value);
		// only filters of form that was sumitted are in request
		$requestFilters = $app->input->get('filter', array(), 'array');
		// stored filters of currently processed form
		$sessionFilters = $app->getUserState($this->paginationcontext . '.filter', array());
		$newFilters = array();
		$filtersChanged = false;
		// set filters of currently processed form according to stored session values and request values
		// note: filters in session are stored as array, filters in model state are stored as objects with name filter.filtername
		foreach ($requestFilters as $name => $value) {
			if (strpos($name, $this->paginationcontext) !== false) {
				$filtername = str_replace($this->paginationcontext, '', $name);
				// Exclude if blacklisted
				if (!in_array($name, $this->filterBlacklist)) {
					$newFilters[$name] = $value;
					$app->setUserState($this->paginationcontext . '.filter.' . $name, $value);
					$this->setState('filter.' . $filtername, $value);
					$filtersChanged = true;
				}
			}
		}
		if ($filtersChanged) {
			$app->setUserState($this->paginationcontext . '.filter', $newFilters);
		} 
		else {
			foreach ($sessionFilters as $name => $value) {
				// use stored filters as state filter
				if (strpos($name, $this->paginationcontext) !== false) {
					$filtername = str_replace($this->paginationcontext, '', $name);
					$this->setState('filter.' . $filtername, $value);
				}
			}
		}
		// Out of the box, with Joomla! it is not possible to have more than one sortable table on a page (no prefix supported as for pagination), so one request can only handle one value for each parameter
		// we add a unique context everywhere to distinguish between different adminForms and make sure that always the right filter_order and filter_order_dir control is filled in the admin form
		$ordering = $app->getUserStateFromRequest($this->paginationcontext . '.ordering', $this->paginationcontext . 'filter_order', $this->getOrderingParamNameFromParams($params), 'string');
		$this->setState('list.ordering', $ordering);
		$direction = strtolower($app->getUserStateFromRequest($this->paginationcontext . '.direction', $this->paginationcontext . 'filter_order_Dir', $params->get('sortdirection', 'asc'), 'string'));
		$this->setState('list.direction', $direction);
		// filter.vfsortording is always submitted through the javascript that is used to order data on click on table header.
		// Therefore we can set the state and user state directly from the $ordering and $direction, without checking the old sessionFilter values
		$this->setState('filter.vfsortordering', $ordering . ' ' . $direction);
		$app->setUserState($this->paginationcontext . '.filter.'.$this->paginationcontext.'vfsortordering', $ordering . ' ' . $direction);
	}
	protected function getOrderingParamNameFromParams($params) {
		$test = $params->get('sortorder', 'id');
		if (is_numeric($test)) {
			return 'a.F'.$test;
		}
		if (strpos($test, 'a.') !== 0) {
			return 'a.'.$test;
		}
		return $test;
	}

	public function getPagination() {
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit, $this->paginationcontext);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$canDo = VisformsHelper::getActions($this->id);
		$canPublish = $this->canPublish();
		$fields = $this->datafields;
		$menu_params = $this->getState('params', new JRegistry());
		$layout = JFactory::getApplication()->input->get('layout', 'data', 'string');
		$isEditLayout = ($layout == "detailedit" || $layout == "dataeditlist") ? true : false;
		$editableonly = ($isEditLayout) ? $menu_params->get('editableonly', 1) : $menu_params->get('editableonly', 0);
		// Select the required fields from the table.
		$query->select($this->getState('list.select', '*'));
		$tn = "#__visforms_" . $this->id;
		$query->from($db->quoteName($tn) . ' AS a');
		if (!empty($canPublish)) {
			$searchfilter = $this->getState('filter.' . 'published');
			if ((isset($searchfilter)) && ($searchfilter != '') && in_array((int) $searchfilter, array(0,1))) {
				$query->where($db->quoteName('published') . ' = ' . $searchfilter);
			}
            else {
                $query->where($db->quoteName('published') . ' IN (0,1)');
            }
		}
		else {
			$query->where($db->quoteName('published') . ' = ' . 1);
		}
		// only use the items specified in the fieldselect list
		if (isset($this->pparams['fieldselect']) && is_array($this->pparams['fieldselect']) && (!empty($fields))) {
			foreach ($this->pparams['fieldselect'] as $name => $value) {
				if (is_numeric($name)) {
					$name = "F" . $name;
				}
				foreach ($fields as $field) {
					// different aproach for fields with multi select options
					if ('F' . $field->id == $name) {
						if (in_array($field->typefield, array('select', 'multicheckbox', 'selectsql', 'multicheckboxsql'))) {
							$viewSelection = '%' .JHtmlVisformsselect::$msdbseparator . $value . JHtmlVisformsselect::$msdbseparator . '%';
							$storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$msdbseparator), $db->quoteName($name), $db->q(JHtmlVisformsselect::$msdbseparator)));
							$query->where('(' . $storedSelections . ' like ' . $db->q($viewSelection) . ')');
						}
						else {
							$query->where($db->quoteName($name) . " = " . $db->quote($value), "AND");
						}
					}
				}
			}
		}

		$hasFrontenddataedit = VisformsAEF::checkAEF(VisformsAEF::$allowFrontEndDataEdit);
		if (!empty($hasFrontenddataedit)) {
			if ($editableonly == 1) {
				if ($canDo->get('core.edit.data')) {
					// get all record sets
				}
				else if ($canDo->get('core.edit.own.data')) {
					$query->where($db->quoteName('created_by') . " = " . $userId);
				}
				else {
					// don't return any record sets
					$query->where($db->quoteName('created_by') . " = -1 ");
				}
			}
		}
		if (!empty($this->visform->ownrecordsonly) && !($isEditLayout)) {
			if (!empty($userId)) {
				$query->where($db->quoteName('created_by') . " = " . $userId);
			}
			else {
				// don't return any record sets
				$query->where($db->quoteName('created_by') . " = -1 ");
			}
		}
		// Filter by search
		$filter = $this->getFilter();
		if (!($filter === '')) {
			$query->where($filter);
		}
		if (!empty($fields)) {
			// apply select filter selctions
			foreach ($fields as $field) {
				// in plugin context use only fields which ar in the plugin field display list
				if ((!empty($this->pparams)) && (!(in_array($field->id, $this->pluginfieldlist)))) {
					continue;
				}
				if (in_array($field->typefield, array('select', 'radio', 'multicheckbox', 'selectsql', 'radiosql', 'multicheckboxsql')) && !empty($field->isfilterfield)) {
					$selectfilter = $this->getState('filter.' . $field->name);
					// 0 is a valid option
					if ((!isset($selectfilter)) || ($selectfilter === '')) {
						continue;
					}
					// select recordsets
					$viewSelection = '%' .JHtmlVisformsselect::$msdbseparator . $selectfilter . JHtmlVisformsselect::$msdbseparator . '%';
					$storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$msdbseparator), $db->quoteName('F' . $field->id), $db->q(JHtmlVisformsselect::$msdbseparator)));
					$query->where('(' . $storedSelections . ' like ' . $db->q($viewSelection) . ')');
					continue;
				}
				// checkbox
				if ($field->typefield == 'checkbox' && !empty($field->isfilterfield)) {
					$selectfilter = $this->getState('filter.' . $field->name);
					if ((!isset($selectfilter)) || ($selectfilter === '')) {
						continue;
					}
					if ($selectfilter == 'checked') {
						$query->where($db->quoteName('F'. $field->id) .' = ' . $db->q($field->attribute_value));
					}
					else {
						$query->where('NOT' . $db->quoteName('F'. $field->id) .' = ' . $db->q($field->attribute_value));
					}
				}
				// radius search
				if ($field->typefield === "location" && !empty($field->allowferadiussearch)) {
					$selectFilterLocation = $this->getState('filter.' . $field->name . '_location');
					$selectFilterLocation = JHtmlVisformslocation::extractDbValue($selectFilterLocation);
					$selectFilterRadius = $this->getState('filter.' . $field->name . '_radius');
					// empty radius means everywhere
					if (empty($selectFilterRadius) || !isset($selectFilterLocation['lat']) || $selectFilterLocation['lat'] === "" || !isset($selectFilterLocation['lng']) || $selectFilterLocation['lng'] === "") {
						continue;
					}
					$earthRadius = (!empty($field->distanceunit) && isset($this->radius[$field->distanceunit])) ? $this->radius[$field->distanceunit] : $this->radius['km'];
					$query->where("SUBSTRING(SUBSTRING_INDEX(F" . $field->id . ", ',', 1),9, (LENGTH(SUBSTRING_INDEX(F" . $field->id . ", ',', 1))-9)) != '' ");
					$query->where("SUBSTRING(SUBSTRING_INDEX(F" . $field->id . ", ',', -1),8, (LENGTH(SUBSTRING_INDEX(F" . $field->id . ", ',', 1))-9)) != '' ");
					$query->where($earthRadius . " * acos( cos( radians(" . $selectFilterLocation['lat'] . "*1) ) * cos( radians( (SUBSTRING(SUBSTRING_INDEX(F" . $field->id . ", ',', 1),9, (LENGTH(SUBSTRING_INDEX(F" . $field->id . ", ',', 1))-9))) *1 ) ) * cos( radians(" . $selectFilterLocation['lng'] . "*1 ) - radians((SUBSTRING(SUBSTRING_INDEX(F" . $field->id . ", ',', -1),8, (LENGTH(SUBSTRING_INDEX(F" . $field->id . ", ',', 1))-9)))*1) ) + sin( radians(" . $selectFilterLocation['lat'] . "*1) ) * sin( radians( (SUBSTRING(SUBSTRING_INDEX(F" . $field->id . ", ',', 1),9, (LENGTH(SUBSTRING_INDEX(F" . $field->id . ", ',', 1))-9)))*1 ) ) ) < " . $selectFilterRadius);
				}
				if ($field->typefield === "date" && !empty($field->isfilterfield)) {
					$formats = explode(';', $field->format);
					$format = $formats[1];
					$searchfilter = $this->getState('filter.' . $field->name . '_min');
					if ((isset($searchfilter)) && ($searchfilter != '') && !empty($searchfilter)) {
						$query->where(' STR_TO_DATE(' . $db->quoteName('F'. $field->id)  . ', ' . $db->quote($format) . ')  >  STR_TO_DATE(' . $db->q($searchfilter)  . ', ' . $db->quote($format) . ')');
					}
					$searchfilter = $this->getState('filter.' . $field->name . '_max');
					if ((isset($searchfilter)) && ($searchfilter != '') && !empty($searchfilter)) {
						$query->where(' STR_TO_DATE(' . $db->quoteName('F'. $field->id)  . ', ' . $db->quote($format) . ')  <  STR_TO_DATE(' . $db->q($searchfilter)  . ', ' . $db->quote($format) . ')');
					}
				}
			}
		}
		// foreach ($this->displayedDefaultDbFields as $fieldname => $name)
		if ((!empty($this->displayedDefaultDbFields)) && isset($this->displayedDefaultDbFields['ismfd'])) {
			$searchfilter = $this->getState('filter.' . 'ismfd');
			if ((isset($searchfilter)) && ($searchfilter != '')) {
				$query->where($db->quoteName('ismfd') . ' = ' . $searchfilter);
			}
		}
		$searchfilter = $this->getState('filter.' . 'mincreated');
		if ((isset($searchfilter)) && ($searchfilter != '') && $searchfilter != $db->getNullDate()) {
			$searchfilter = JFactory::getDate($searchfilter)->toSql();
			$query->where($db->quoteName('created') . ' >  ' . $db->q($searchfilter));
		}
		$searchfilter = $this->getState('filter.' . 'maxcreated');
		if ((isset($searchfilter)) && ($searchfilter != '') && $searchfilter != $db->getNullDate()) {
			$searchfilter = JFactory::getDate($searchfilter)->toSql();
			$query->where($db->quoteName('created') . ' <  ' . $db->q($searchfilter));
		}
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'id');
		if (is_numeric($orderCol)) {
			$orderCol = "F" . $orderCol;
		}
		$this->setState('list.ordering', $orderCol);
		$orderDirn = $this->state->get('list.direction', 'asc');
		// we store dates as strings in database. If sort order field is of type date we have to convert the strings before we order the recordsets
		if (!empty($fields)) {
			foreach ($fields as $field) {
				$fname = 'F' . $field->id;
				if (($field->typefield == 'date') && (($orderCol == $fname) || ($orderCol == 'a.' . $fname))) {
					$formats = explode(';', $field->format);
					$format = $formats[1];
					$orderCol = ' STR_TO_DATE(' . $orderCol . ', ' . $db->quote($format) . ') ';
					break;
				}
				if ((($field->typefield == 'number') || ($field->typefield == 'calculation')) && (($orderCol == $fname) || ($orderCol == 'a.' . $fname))) {
					$orderCol = '(' . $orderCol . ' * 1)';
				}
			}
		}
		$query->order($orderCol . ' ' . $orderDirn);
		return $query;
	}

	public function getDatafields() {
		// Lets load the data if it doesn't already exist
		// exclude all fieldtypes that should not be published in frontend (submits, resets, fieldseparator)
		$datafields = $this->datafields;
		if (empty($datafields)) {
			$fieldorder = (!empty($this->fieldOrder)) ? $this->fieldOrder : 'ordering';
			$db = JFactory::getDbO();
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();
			$frontaccess = implode(", ", $groups);
			$excludedFieldTypes = "'reset', 'submit', 'image', 'fieldsep'";
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__visfields'))
				->where($db->quoteName('fid') . " = " . $this->id)
				->where($db->quoteName('published') . ' = ' . 1)
				->where($db->quoteName('frontaccess') . " in (" . $frontaccess . ")")
				->where($db->quoteName('typefield') . "not in (" . $excludedFieldTypes . ")")
				->where('(' . $db->qn('frontdisplay') . ' is null or ' . $db->qn('frontdisplay') . ' in (1,2,3))')
				->order($db->quoteName($fieldorder) . " asc");
			try {
				$db->setQuery($query);
				$datafields = $db->loadObjectList();
			}
			catch (Exception $ex) {

			}
			$n = count($datafields);
			for ($i = 0; $i < $n; $i++) {
				$registry = new JRegistry;
				$registry->loadString($datafields[$i]->defaultvalue);
				$datafields[$i]->defaultvalue = $registry->toArray();

				foreach ($datafields[$i]->defaultvalue as $name => $value) {
					// make names shorter and set all defaultvalues as properties of field object
					$prefix = 'f_' . $datafields[$i]->typefield . '_';
					if (strpos($name, $prefix) !== false) {
						$key = str_replace($prefix, "", $name);
						$datafields[$i]->$key = $value;
					}
				}

				// delete defaultvalue array
				unset($datafields[$i]->defaultvalue);
				if (in_array($datafields[$i]->typefield, $this->unSortable)){
					$datafields[$i]->unSortable = true;
				} 
				else {
					$datafields[$i]->unSortable = false;
				}
				if ($datafields[$i]->typefield == 'file') {
					$subVersion = VisformsAEF::getVersion(VisformsAEF::$subscription);
					if (version_compare($subVersion, '3.2.1', 'lt')) {
						$datafields[$i]->displayImgAsImgInList = 0;
						$datafields[$i]->displayImgAsImgInDetail = 0;
					}
				}
			}
			$this->datafields = $datafields;
		}
		return $datafields;
	}

	public function getDetail() {
		$db = JFactory::getDbO();
		$app = JFactory::getApplication();
		$array = $app->input->get('cid', array(), 'ARRAY');
		ArrayHelper::toInteger($array);
		$menu_params = $this->getState('params', new JRegistry());
		$layout = $app->input->get('layout', 'data', 'string');
		$isEditLayout = ($layout == "detailedit" || $layout == "dataeditlist") ? true : false;
		$editableonly = ($isEditLayout) ? $menu_params->get('editableonly', 1) : $menu_params->get('editableonly', 0);
		$canPublish = $this->canPublish();
		$canDo = VisformsHelper::getActions($this->id);
		$id = (int) $array[0];
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__visforms_' . $this->id))
			->where($db->quoteName('id') . " = " . $id);
		// if a user can publish/unpublish a recordset, unpublished recordsets are displayed in the list view and the user can display a details view of the unpublished record as well
		if (empty($canPublish)) {
			$query->where($db->quoteName('published') . ' = ' . 1);
		}
        else {
            $query->where($db->quoteName('published') . ' IN (0,1)');
        }
		$hasFrontenddataedit = VisformsAEF::checkAEF(VisformsAEF::$allowFrontEndDataEdit);
		if (!empty($hasFrontenddataedit)) {
			if ($editableonly == 1) {
				if ($canDo->get('core.edit.data')) {
					// get all record sets
				} else if ($canDo->get('core.edit.own.data')) {
					$query->where($db->quoteName('created_by') . " = " . $userId);
				} else {
					// don't return any record sets
					$query->where($db->quoteName('created_by') . " = -1 ");
				}
			}
		}
		if (!empty($this->visform->ownrecordsonly) && !($isEditLayout)) {
			if (!empty($userId)) {
				$query->where($db->quoteName('created_by') . " = " . $userId);
			} else {
				// don't return any record sets
				$query->where($db->quoteName('created_by') . " = -1 ");
			}
		}
		try {
			$db->setQuery($query);
			$detail = $db->loadObject();
		}
		catch (Exception $ex) {
			return false;
		}
		// for fields of type select, radio, multicheckbox and checkbox display option label in data view instead of the stored option values
		// frontend data edit up to version 1.3.0 uses this function to get stored user inputs for the data edit view
		// do not replace stored values in this case
		if ((!empty($detail)) && ($layout != 'edit')) {
			$fields = $this->datafields;
			foreach ($fields as $field) {
				$detailfieldname = "F" . $field->id;
				if (in_array($field->typefield, array('select', 'radio', 'multicheckbox'))) {
					$detailfieldvalue = $detail->$detailfieldname;
					if ((!isset($detailfieldvalue)) || ($detailfieldvalue === '') || (empty($field->list_hidden))) {
						continue;
					}
					$newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToOptionLabel($detailfieldvalue, $field->list_hidden);
					$newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
					$detail->$detailfieldname = $newitemfieldvalue;
				}
				if (in_array($field->typefield, array('selectsql', 'radiosql', 'multicheckboxsql'))) {
					$detailfieldvalue = $detail->$detailfieldname;
					if ((!isset($detailfieldvalue)) || ($detailfieldvalue === '')) {
						continue;
					}
					$newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToSqlOptionLabel($detailfieldvalue, $field->sql);
					$newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
					$detail->$detailfieldname = $newitemfieldvalue;
				}

			if ($field->typefield == 'location') {
				$detail->$detailfieldname = VisformsHelper::registryArrayFromString($detail->$detailfieldname);
				if (!empty($field->displayAsMapInDetail)) {
					$detail->requiresJs = true;
				}
			}
		}
		}
		return $detail;
	}

	public function getForm() {
		$form = $this->visform;
		$hassub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (empty($form)) {
			$db = JFactory::getDbO();
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__visforms'))
				->where($db->quoteName('id') . " = " . $this->id)
				->where($db->quoteName('published') . ' = ' . 1)
				->where($db->quoteName('saveresult') . ' = ' . 1);
			$db->setQuery($query);
			$form = $db->loadObject();
			if (empty($form)) {
				return $form;
			}
			$registry = new JRegistry;
			// Convert frontendsettings field to an array
			$registry->loadString($form->frontendsettings);
			$form->frontendsettings = $registry->toArray();
			foreach ($form->frontendsettings as $name => $value) {
				if (($name == 'ownrecordsonly') && (empty($hassub))) {
					$value = 0;
				}
				if (($name == 'displaycounter') && (empty($hassub))) {
					$value = 0;
				}
				// make names shorter and set all frontendsettings as properties of form object
				$form->$name = $value;
			}
			$form->mapCounter = 0;
			$form->hasLocationRadiusSearch = false;
			$form = $this->cleanForm($form);
		}
		return $form;
	}

	protected function cleanForm($form) {
		if (!isset($form->displaydetail)) {
			$form->displaydetail = 0;
		}
		if (!isset($form->hideemptyfieldsindetail)) {
			$form->hideemptyfieldsindetail = 0;
		}
		// each data view has to check, whether do display the overheadfiels or not. Use only one boolean option, and make sure it is set.
		$displayParameters = array ('displayip', 'displayid', 'displaycreated', 'displaycreatedtime', 'displayismfd', 'displaymodifiedat', 'displaymodifiedattime', 'displaypdfexportbutton');
		foreach ($displayParameters as $parameter) {
			if (!isset($form->$parameter)) {
				$form->$parameter = 0;
			}
			// display in list view
			$listParamName = $parameter . '_list';
			$form->$listParamName = ($form->$parameter == "1" || $form->$parameter == "2") ? true : false;
			// display in detail vies
			$detailParamName = $parameter . '_detail';
			$form->$detailParamName = ($form->$parameter == "1" || $form->$parameter == "3") ? true : false;
			// display in any view of content plugin data view
			$plgParamName = $parameter . '_plg';
			$form->$plgParamName = (!empty($this->pparams) && $this->pparams[$parameter] == "true" && ($form->$parameter == "1" || $form->$parameter == "2" || $form->$parameter == "3")) ? true : false;
		}
		return $form;
	}

	private function getMenuItem() {
		$app = JFactory::getApplication();
		$menu = $app->getMenu()->getActive();
		$lang = JFactory::getLanguage();
		if (!$menu) {
			$menu = $app->getMenu()->getDefault($lang->getTag());
		}

		return $menu;
	}

	public function getFilterForm($data = array(), $loadData = true) {
		$menuParams = $this->state->get('params');
		// we need to add the path explicitely for use with plugin dataview
		JForm::addFormPath(JPATH_ROOT . '/components/com_visforms/models/forms');
		$form = parent::getFilterForm($data, false);
		if (!empty($form)) {
			$fields = $this->datafields;
			$layout = JFactory::getApplication()->input->get('layout', 'data', 'string');
			$isSubscriptionLayout = ($layout == "detailedit" || $layout == "dataeditlist" || isset($this->pparams)) ? true : false;
			$subfilesVersion = VisformsAEF::getVersion(VisformsAEF::$subFiles);
			$searchfieldxml = new SimpleXMLElement('<field
                name="search"
                type="text"
                label="COM_VISFORMS_FILTER_SEARCH_DESC"
                hint="JSEARCH_FILTER"
            />');
			$form->setField($searchfieldxml, 'filter');
			$form->setFieldAttribute('search', 'name', $this->paginationcontext . 'search', 'filter');
			$showCreatedFilter = $menuParams->get('show_filter_created', false);
			if ($showCreatedFilter === "true" || $showCreatedFilter == 1) {
				$minCreatedfieldxml = new SimpleXMLElement('<field
                name="mincreated"
                type="calendar"
                label="COM_VISFORMS_FILTER_CREATED_AFTER"
                hint="COM_VISFORMS_FILTER_CREATED_AFTER"
                >
            </field>');
				$form->setField($minCreatedfieldxml, 'filter');
				$form->setFieldAttribute('mincreated', 'name', $this->paginationcontext . 'mincreated', 'filter');
				$maxCreatedfieldxml = new SimpleXMLElement('<field
                name="maxcreated"
                type="calendar"
                label="COM_VISFORMS_FILTER_CREATED_BEFORE"
                hint="COM_VISFORMS_FILTER_CREATED_BEFORE"
                >
            </field>');
				$form->setField($maxCreatedfieldxml, 'filter');
				$form->setFieldAttribute('maxcreated', 'name', $this->paginationcontext . 'maxcreated', 'filter');
			}
			if (array_key_exists('ismfd', $this->displayedDefaultDbFields)) {
				$ismfdfieldxml = new SimpleXMLElement('<field
                    name="ismfd"
                    type="list"
                    label="COM_VISFORMS_FILTER_ISMFD"
                    description="COM_VISFORMS_FILTER_ISMFD_DESCR"
                    >
                    <option value="">COM_VISFORMS_OPTION_SELECT_ISMFD</option>
                    <option value="1">
                            JYES</option>
                        <option value="0">
                            JNO</option>
                </field>');
				$form->setField($ismfdfieldxml, 'filter');
				$form->setFieldAttribute('ismfd', 'name', $this->paginationcontext . 'ismfd', 'filter');
			}
			$canPublish = $this->canPublish();
			if (!empty($canPublish)) {
				$publishedfieldxml = new SimpleXMLElement('<field
                    name="published"
                    type="list"
                    label="COM_VISFORMS_FILTER_PUBLISHED"
                    description="COM_VISFORMS_FILTER_PUBLISHED_DESC"
                    >
                    <option value="">JOPTION_SELECT_PUBLISHED</option>
                    <option value="1">
                            JPUBLISHED</option>
                        <option value="0">
                            JUNPUBLISHED</option>
                </field>');
				$form->setField($publishedfieldxml, 'filter');
				$form->setFieldAttribute('published', 'name', $this->paginationcontext . 'published', 'filter');
			}
			if (!$isSubscriptionLayout || (version_compare($subfilesVersion, '3.2.1', 'ge'))) {
				$xml =
					'<field
				name="vfsortordering"
				type="list"
				label="COM_VISFORMS_LIST_FULL_ORDERING"
				description="COM_VISFORMS_LIST_FULL_ORDERING_DESC"
				onchange="vttableFullOrdering' . $this->paginationcontext . '(this);"
				class="btn"
				default="a.id asc"
				>
	            <option value="a.id asc">COM_VISFORMS_SORT_ID_ASC</option>
				<option value="a.id desc">COM_VISFORMS_SORT_ID_DESC</option>';
					if (!empty($canPublish)) {
						$xml .= '<option value="a.published asc">COM_VISFORMS_SORT_PUBLISHED_ASC</option>
				<option value="a.published desc">COM_VISFORMS_SORT_PUBLISHED_DESC</option>';
					}
					if (array_key_exists('created', $this->displayedDefaultDbFields)) {
						$xml .= '<option value="a.created asc">COM_VISFORMS_SORT_DATE_ASC</option>
				<option value="a.created desc">COM_VISFORMS_SORT_DATE_DESC</option>';
					}
					if (array_key_exists('ipaddress', $this->displayedDefaultDbFields)) {
						$xml .= '<option value="a.ipaddress asc">COM_VISFORMS_SORT_IP_ASC</option>
				<option value="a.ipaddress desc">COM_VISFORMS_SORT_IP_DESC</option>';
					}
					if (array_key_exists('ismfd', $this->displayedDefaultDbFields)) {
						$xml .= '<option value="a.ismfd asc">COM_VISFORMS_SORT_ISMFD_ASC</option>
				<option value="a.ismfd desc">COM_VISFORMS_SORT_ISMFD_DESC</option>';
					}
					if (array_key_exists('modified', $this->displayedDefaultDbFields)) {
						$xml .= '<option value="a.modified asc">COM_VISFORMS_SORT_MODIFIED_AT_ASC</option>
				<option value="a.modified desc">COM_VISFORMS_SORT_MODIFIED_AT_DESC</option>';
					}

				foreach ($fields as $field) {
					if ((!empty($this->pparams))) {
						if (!(in_array($field->id, $this->pluginfieldlist))) {
							continue;
						}
					} // only search filter for fields which are displayed in list view
					else if ((!empty($field->frontdisplay))) {
						if ($field->frontdisplay == 3) {
							continue;
						}
					}
					if (empty($field->unSortable)) {
						$xml .= '<option value="a.F' . $field->id . ' asc">' . htmlspecialchars($field->label, ENT_COMPAT, 'UTF-8') . ' ' . JText::_("COM_VISFORMS_ASC") . '</option>';
						$xml .= '<option value="a.F' . $field->id . ' desc">' . htmlspecialchars($field->label, ENT_COMPAT, 'UTF-8') . ' ' . JText::_("COM_VISFORMS_DESC") . '</option>';
					}
				}

				$xml .= '</field>';

				$sortorderfieldxml = new SimpleXMLElement($xml);
				$form->setField($sortorderfieldxml, 'filter');
				$form->setFieldAttribute('vfsortordering', 'name', $this->paginationcontext . 'vfsortordering', 'filter');
			}

		// if we come from the dataview plugin, only show filter of fields which are in the plugins fieldlist
			foreach ($fields as $field) {
				if ((!empty($this->pparams))) {
					if (!(in_array($field->id, $this->pluginfieldlist))) {
						continue;
					}
				} // only search filter for fields which are displayed in list view
				else if ((!empty($field->frontdisplay))) {
					if ($field->frontdisplay == 3) {
						continue;
					}
				}
				if (in_array($field->typefield, array('select', 'radio', 'multicheckbox', 'selectsql', 'radiosql', 'multicheckboxsql')) && $field->isfilterfield) {
					if ($this->getListBoxFilterField($field) !== false) {
						$addFilterField = new SimpleXMLElement($this->getListBoxFilterField($field));
						$form->setField($addFilterField, 'filter');
					}
				}
				if ($field->typefield == 'checkbox' && $field->isfilterfield) {
					if ($this->getCheckboxFilterField($field) !== false) {
						$addFilterField = new SimpleXMLElement($this->getCheckboxFilterField($field));
						$form->setField($addFilterField, 'filter');
					}
				}
				if ($field->typefield === "location" && !empty($field->allowferadiussearch)) {
					$this->visform->hasLocationRadiusSearch = true;
					$locationSearchXml = '<field
						name="' . $this->paginationcontext . $field->name . '"
						class="placessearchbox"
						type="text"
						label="JOPTION_FILTER_PUBLISHED"
						hint="Standort"
						onchange="geocodeSearchAddress(this);"
						/>';
					$addFilterField = new SimpleXMLElement($locationSearchXml);
					$form->setField($addFilterField, 'filter');

					$locationLocationXml = '<field
						name="' . $this->paginationcontext . $field->name . '_location"
						type="hidden"
						/>';
					$addFilterField = new SimpleXMLElement($locationLocationXml);
					$form->setField($addFilterField, 'filter');

					$options = '<option value="">' . JText::sprintf('COM_VISFORMS_FILTER_SELECT_SELECT_A_VALUE', 'Radius') . '</option>';
					foreach (array(10, 20, 50, 100, 1000) as $fieldoption) {
						$options .= '<option value="' . $fieldoption . '">' . $fieldoption . '</option>';
					}
					$locationRadiusXml = '<field
						name="' . $this->paginationcontext . $field->name . '_radius"
						type="list"
						label="JOPTION_FILTER_PUBLISHED"
						>' . $options . '
						
					</field>';
					$addFilterField = new SimpleXMLElement($locationRadiusXml);
					$form->setField($addFilterField, 'filter');
				}
				if ($field->typefield === "date" && !empty($field->isfilterfield)) {
					$formats = explode(';', $field->format);
					$dateFieldSearchXml = '<field
		                    name="' . $this->paginationcontext . $field->name . '_min"
		                    type="calendar"
		                    label="COM_VISFORMS_FILTER_DATE_AFTER"
		                    hint="COM_VISFORMS_FILTER_DATE_AFTER"
		                    format="'.$formats[1].'"
		                >
		            </field>';
					$addFilterField = new SimpleXMLElement($dateFieldSearchXml);
					$form->setField($addFilterField, 'filter');
					$form->setFieldAttribute($this->paginationcontext . $field->name . '_min', 'label', $field->label . ' ' . JText::_('COM_VISFORMS_FILTER_DATE_AFTER'), 'filter');
					$form->setFieldAttribute($this->paginationcontext . $field->name . '_min', 'hint', $field->label . ' ' . JText::_('COM_VISFORMS_FILTER_DATE_AFTER'), 'filter');
					$dateFieldSearchXml = '<field
		                    name="' . $this->paginationcontext . $field->name . '_max"
		                    type="calendar"
		                    label="COM_VISFORMS_FILTER_DATE_BEFORE"
		                    hint="COM_VISFORMS_FILTER_DATE_BEFORE"
		                    format="'.$formats[1].'"
		                >
		            </field>';
					$addFilterField = new SimpleXMLElement($dateFieldSearchXml);
					$form->setField($addFilterField, 'filter');
					$form->setFieldAttribute($this->paginationcontext . $field->name . '_max', 'label', $field->label . ' ' . JText::_('COM_VISFORMS_FILTER_DATE_BEFORE'), 'filter');
					$form->setFieldAttribute($this->paginationcontext . $field->name . '_max', 'hint', $field->label . ' ' . JText::_('COM_VISFORMS_FILTER_DATE_BEFORE'), 'filter');
				}
			}
		}

		$data = $this->loadFormData();
		$form->bind($data);
		return $form;
	}

	public function getActiveFilters() {
		$activeFilters = array();

		if (!empty($this->filter_fields)) {
			foreach ($this->filter_fields as $filter) {
				$contextfreefiltername = str_replace($this->paginationcontext, '', $filter);
				$filterName = 'filter.' . $contextfreefiltername;

				if (property_exists($this->state, $filterName) && (!empty($this->state->{$filterName}) || is_numeric($this->state->{$filterName}))) {
					$activeFilters[$filter] = $this->state->get($filterName);
				}
			}
		}

		return $activeFilters;
	}

	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->paginationcontext, new stdClass);

		// Pre-fill the list options
		if (!property_exists($data, 'list')) {
			$data->list = array(
				'direction' => $this->state->{'list.direction'},
				'limit' => $this->state->{'list.limit'},
				'ordering' => $this->state->{'list.ordering'},
				'start' => $this->state->{'list.start'}
			);
		}

		return $data;
	}

	protected function getFilter() {
		// Get Filter parameters
		$fields = $this->datafields;
		$searchfilter = $this->getState('filter.search');
		$filter = '';
		if (($searchfilter != '') && (!empty($fields))) {
			$referenceDate = JFactory::getDate('now', 'UTC');
			$userTimeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
			$offsetInSeconds = $userTimeZone->getOffset($referenceDate);
			$sign =($offsetInSeconds < 0) ? '-' : '+';
			$offsetInSeconds = abs($offsetInSeconds);
			$filter .= " (";
			foreach ($fields as $field) {
				if ((!empty($this->pparams)) && (!(in_array($field->id, $this->pluginfieldlist)))) {
					continue;
				}
				if ($field->typefield == 'signature') {
					continue;
				}
				// string search in all fields which are not displayed as filter
				if (empty($field->isfilterfield)) {
					$prop = "F" . $field->id;
					$filter .= " upper(" . $prop . ") like upper('%" . $searchfilter . "%') or ";
				}
			}
			foreach ($this->displayedDefaultDbFields as $fieldname => $name) {
				$dateformat = JText::_('DATE_FORMAT_LC4');
				$mySqlDateFormat = str_replace('d', '%d', str_replace('m', '%m', str_replace('Y', '%Y', $dateformat)));
				if ((($fieldname == 'created') && ($name == 'displaycreated')) || (($fieldname == 'modified') && ($name == 'displaymodifiedat'))) {
					$filter .= " from_unixtime((unix_timestamp(".$fieldname.") ".$sign." ".$offsetInSeconds ."), '".$mySqlDateFormat."') like '%".$searchfilter."%' or ";
				} 
				else if ((($fieldname == 'created') && ($name == 'displaycreatedtime')) || (($fieldname == 'modified') && ($name == 'displaymodifiedattime'))) {
					$filter .= " from_unixtime((unix_timestamp(".$fieldname.") ".$sign." ".$offsetInSeconds ."), '".$mySqlDateFormat." %H:%i:%s') like '%".$searchfilter."%' or ";
				} 
				else if ($fieldname != 'ismfd') {
					$filter .= " " . $fieldname . " like '%" . $searchfilter . "%' or ";
				}
			}
			$filter = rtrim($filter, 'or ');
			$filter = $filter . " )";
		}
		return $filter;
	}

	public function getContext() {
		if (!empty($this->paginationcontext)) {
			return $this->paginationcontext;
		}
		return '';
	}

	protected function getListBoxFilterField($field) {
		if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')) && empty($field->list_hidden)) {
			return false;
		}
		if (in_array($field->typefield, array('select', 'radio', 'multicheckbox'))) {
			$fieldoptions = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
		}
		else {
			$fieldoptions = JHtmlVisformsselect::getOptionsFromSQL($field->sql);
		}
		$options = '<option value="">' . JText::sprintf('COM_VISFORMS_FILTER_SELECT_SELECT_A_VALUE', htmlspecialchars($field->label, ENT_COMPAT, 'UTF-8')) . '</option>';
		if (!empty($fieldoptions)) {
			foreach ($fieldoptions as $fieldoption) {
				$options .= '<option value="' . htmlspecialchars($fieldoption['value'], ENT_COMPAT, 'UTF-8') . '">' . htmlspecialchars($fieldoption['label'], ENT_COMPAT, 'UTF-8') . '</option>';
			}
		}
		$xmlstring = '<field
			name="' . $this->paginationcontext . $field->name . '"
			type="list"
			label="JOPTION_FILTER_PUBLISHED"
			>' . $options . '
			
		</field>';
		return $xmlstring;
	}

	protected function getCheckboxFilterField($field) {
		if ($field->typefield = 'checkbox' && !isset($field->attribute_value)) {
			return false;
		}
		$options = '<option value="">' . JText::sprintf('COM_VISFORMS_FILTER_SELECT_SELECT_A_VALUE', htmlspecialchars($field->label, ENT_COMPAT, 'UTF-8')) . '</option>';
		$options .= '<option value="checked">' . htmlspecialchars($field->attribute_value, ENT_COMPAT, 'UTF-8') . '</option>';
		$options .= '<option value="unchecked">' .JText::_('COM_VISFORMS_NOT')  . ' '. htmlspecialchars($field->attribute_value, ENT_COMPAT, 'UTF-8') . '</option>';
		$xmlstring = '<field
			name="' . $this->paginationcontext . $field->name . '"
			type="list"
			label="JOPTION_FILTER_PUBLISHED"
			>' . $options . '
			
		</field>';
		return $xmlstring;
	}

	public function getItems() {
		$items = parent::getItems();
		$fields = $this->datafields;
		if ((empty($items)) || empty($fields)) {
			return $items;
		}
		$n = count($items);
		for ($i = 0; $i < $n; $i++) {
			foreach ($fields as $field) {
				$itemfieldname = "F" . $field->id;
				// display options labels for selects, radios, multicheckboxes and checkboxes in frontend data views not the stored option values
				if (in_array($field->typefield, array('select', 'radio', 'multicheckbox'))) {
					$itemfieldvalue = $items[$i]->$itemfieldname;
					if ((!isset($itemfieldvalue)) || ($itemfieldvalue === '') || (empty($field->list_hidden))) {
						continue;
					}
					$newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToOptionLabel($itemfieldvalue, $field->list_hidden);
					$newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
					$items[$i]->$itemfieldname = $newitemfieldvalue;
				}
				if (in_array($field->typefield, array('selectsql', 'radiosql', 'multicheckboxsql'))) {
					$itemfieldvalue = $items[$i]->$itemfieldname;
					if ((!isset($itemfieldvalue)) || ($itemfieldvalue === '')) {
						continue;
					}
					$newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToSqlOptionLabel($itemfieldvalue, $field->sql);
					$newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
					$items[$i]->$itemfieldname = $newitemfieldvalue;
				}
				if ($field->typefield == 'location') {
					$items[$i]->$itemfieldname = VisformsHelper::registryArrayFromString($items[$i]->$itemfieldname);
				}
			}
		}
		return $items;
	}

	// only use in list views!
	protected function setDisplayedDefaultDbFields() {
		$displayedDefaultDbFields = $this->displayedDefaultDbFields;
		if (empty($displayedDefaultDbFields)) {
			$form = $this->visform;
			$displayedDefaultDbFields = array();
			$formParamNames = array('displayip' => 'ipaddress', 'displaycreated' => 'created', 'displaycreatedtime' => 'created', 'displayismfd' => 'ismfd', 'displaymodifiedat' => 'modified', 'displaymodifiedattime' => 'modified');
			foreach ($formParamNames as $name => $fieldname) {
				if ((isset($form->$name)) && (in_array($form->$name, array('1', '2')))) {
					if ((empty($this->pparams)) || ((isset($this->pparams[$name])) && ($this->pparams[$name] === 'true'))) {
						// use named array, in order to prevent two elements with value created
						$displayedDefaultDbFields[$fieldname] = $name;
					}
				}
			}
		}
		return $displayedDefaultDbFields;
	}

	protected function canPublish() {
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$canDo = VisformsHelper::getActions($this->id);
		$vffronteditVersion = JHtmlVisforms::getFrontendDataEditVersion();
		$layout = JFactory::getApplication()->input->get('layout', 'data', 'string');
		$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowFrontEndDataEdit);
		if ((!empty($canFrontendEdit)) && ($canDo->get('core.edit.data.state'))
			&& (version_compare($vffronteditVersion, '1.3.0', 'ge'))
			&& (($layout == 'detailedit') || ($layout == 'dataeditlist'))) {
			return true;
		}
		return false;
	}

	// only use in list views!
	protected function setPluginFieldList() {
		$pluginfieldlist = array();
		if ((!empty($this->pparams)) && (!empty($this->pparams['fieldlist']))) {
			$rawpluginfieldlist = explode(',', $this->pparams['fieldlist']);
			$fields = $this->datafields;
			foreach ($rawpluginfieldlist as $value) {
				$fieldid = trim($value);
				foreach ($fields as $field) {
					// if any sort of frontdisplay is enable for the field in field configuration, it is displayed by the plugin vfdataview
					if (($field->id == $fieldid) && (in_array($field->frontdisplay, array('1', '2', '3')))) {
						$pluginfieldlist[] = $fieldid;
					}
				}
			}
		}
		return $pluginfieldlist;
	}

	// deprecated, use JHTMLVisforms::checkDataViewMenuItemExists
	// keep for compatibility with older subscription versions.
	public function checkDataViewMenuItemExists() {
		return JHTMLVisforms::checkDataViewMenuItemExists($this->id);
	}

	public function getMenuId() {
		if ($menu = $this->getMenuItem()) {
			return ($menu->id) ? $menu->id : 0;
		}
		return 0;
	}
}
