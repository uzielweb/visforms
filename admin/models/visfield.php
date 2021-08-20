<?php
/**
 * visfield model for Visforms
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
require_once (JPATH_ADMINISTRATOR . '/components/com_visforms/models/itemmodelbase.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_visforms/models/visform.php');
require_once (JPATH_ROOT . '/components/com_visforms/lib/validate.php');
require_once JPATH_ROOT . '/administrator/components/com_visforms/lib/visformsSql.php';
use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

class VisformsModelVisfield extends VisFormsItemModelBase
{
	protected $aefList;
	protected $allowedCalcualtionPlaceholderFieldTypes = array('number', 'select', 'calculation', 'hidden', 'checkbox', 'text', 'date', 'radio');

    public function __construct($config = array()) {
        $config['events_map'] = array(
            'delete' => 'visforms',
            'save' => 'visforms',
            'change_state' => 'visforms'
            );
        $config['event_before_save'] = 'onVisformsBeforeJFormSave';
        $config['event_after_save'] = 'onVisformsAfterJFormSave';
        $config['event_before_delete'] = 'onVisformsBeforeJFormDelete';
        $config['event_after_delete'] = 'onVisformsAfterJFormDelete';
        $config['event_change_state'] = 'onVisformsJFormChangeState';
	    $this->aefList = VisformsAEF::getAefList();
        parent::__construct($config);
    }
	
	public function batch($commands, $pks, $contexts) {
		// sanitize field ids
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// remove any values of zero
		if (array_search(0, $pks, true)) {
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks)) {
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$result = $this->batchCopy($commands, $pks, $contexts);
		if ( !is_array($result)) {
			return false;
		}

		// clear the cache
		$this->cleanCache();

		return true;
	}

	protected function batchCopy($commands, $oldFields, $contexts) {
		$isFormCopy = ArrayHelper::getValue($commands,'isFormCopy', false);
		$newFid = ArrayHelper::getValue($commands,'form_id', 0);
		// array is used to mend restricts and restrictions in copied fields
		$copyFormOldNewFieldsIdMap = array();
		$isFieldInFormCopy = false;
		$table = $this->getTable();
		
		if (empty($newFid)) {
			$this->setError((JText::_('COM_VISFORMS_ERROR_BATCH_NO_FORM_SELECTED')));
			return false;
		}
		$i = 0;

		// check that the user has create permission for this form
		$extension = JFactory::getApplication()->input->get('option', '');
		$user = JFactory::getUser();
		if (!$user->authorise('core.create', $extension . '.visform.' . $newFid)) {
			$this->setError(JText::_('COM_VISFORMS_FIELD_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}

		// parent exists so we let's proceed
		while (!empty($oldFields)) {
			// pop the first ID off the stack
			$pk = array_shift($oldFields);
			$table->reset();

			// check that the row actually exists
			if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // fatal error
					$this->setError($error);
					return false;
				}
				else {
                    // not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			if ($newFid == $table->fid) {
                // we copy a field into the same form so Alter the label & alias
				$data = $this->generateNewTitle('', $table->name, $table->label);
				$table->label = $data['0'];
				$table->name = $data['1'];
				$isFieldInFormCopy = true;
				//Remove values in database field restrictions
				$table->restrictions = "";
			}
			else {
				$formIdMapper = array($table->fid => $newFid);
				//we either copy an form with it's field or we copy a single field into another form
                // alter form id
				$table->fid = $newFid;
				if ($isFormCopy !== true) {
					//we copy a field into another form, where the restrictor fields do not exist
                    // reset values in options that reference other fields like _validate_equalTo
					$table->defaultvalue = VisformsConditionsHelper::removeRestrictsValues($table->defaultvalue, $table->name, true);
				}
				// remove values in database field restrictions, we set them anew if necessary
				$table->restrictions = "";
			}

			// reset the ID because we are making a copy
			$table->id = 0;
			$unpublish = ArrayHelper::getValue($commands, 'unpublish', true);
			// set to unpublished
			$table->published = ($unpublish) ?  0 : $table->published ;
			// delete ordering to get the next ordering number
			$table->ordering = '';

			// check the row
			if (!$table->check()) {
                $this->setError($table->getError());
				return false;
			}

			// store the row
			if (!$table->store()) {
                $this->setError($table->getError());
				return false;
			}

			// are data saved for the table the copied fields belong to?
			// then we have to create a data table field
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			// select Ids of Fields of copied form in Table visfields
			$query
				->select($db->qn('saveresult'))
				->from($db->qn('#__visforms'))
				->where($db->qn('id') . ' = ' .$table->fid);

			$db->setQuery($query);
			$saveResult = $db->loadResult();
			$this->createDataTableFields($table->fid, $table->id, $saveResult);

			// get the new item ID
			$newId = $table->get('id');
			// we copy a complete form and must adapt conditional fields to new form
			if ($isFormCopy === true) {
                // create an item in the field map array
				$copyFormOldNewFieldsIdMap[$pk] = $newId;
			}
			if ($isFieldInFormCopy === true) {
                $oldRestricts =  VisformsConditionsHelper::setRestrictsFromDb($newId, $table->fid);
				if (!empty($oldRestricts)) {
                    // only have to add new restrictions to existing fields
					VisformsConditionsHelper::setRestriction($oldRestricts);
				}
			}
			// add the new ID to the array
			$newIds[$i]	= $newId;
			$i++;
		}

		if (!empty($copyFormOldNewFieldsIdMap)) {
		    VisformsConditionsHelper::setConditionsInCopiedFields($copyFormOldNewFieldsIdMap, $newFid);
			// fix references to data table of the old form and data table fields of the old form in radiosql, selectsql, multicheckboxsql field if we batch copy form
			if ($isFormCopy === true) {
				$helper = new visFormsImportHelper($copyFormOldNewFieldsIdMap, $formIdMapper);
				$helper->adaptFieldsSqlStatementToNewForm();
			}
		}

		// clean the cache
		$this->cleanCache();

		return $newIds;
	}

	public function importFieldData($oldFields, $newFid) {
		$copyFormOldNewFieldsIdMap = array();
		$table = $this->getTable();
		$i = 0;

		// check that the user has create permission for this form
		$extension = JFactory::getApplication()->input->get('option', '');
		$user = JFactory::getUser();
		if (!$user->authorise('core.create', $extension . '.visform.' . $newFid)) {
			$this->setError(JText::_('COM_VISFORMS_FIELD_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}

		// parent exists so we let's proceed
		while (!empty($oldFields)) {
			$pk = array_shift($oldFields);
			$oldFieldId = $pk['id'];
			$pk['id'] = 0;
			$pk['fid'] = $newFid;
			$table->reset();
			$table->bind($pk);
			// check the row
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// store the row
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
			// get the new item ID
			$newId = $table->get('id');
			$copyFormOldNewFieldsIdMap[$oldFieldId] = $newId;
		}
		if (!empty($copyFormOldNewFieldsIdMap)) {
			VisformsConditionsHelper::setConditionsInCopiedFields($copyFormOldNewFieldsIdMap, $newFid);
		}

		// clean the cache
		$this->cleanCache();
		return $copyFormOldNewFieldsIdMap;
	}
	
	public function save($data) {
    	// ToDo if selected field type comes from subscription feature which is not available, set an error message and return false
		// Pagebreak, Cal, Map, radiosql, selectsql, multicheckboxsql, signature
        $app = JFactory::getApplication();
		$aefCalVersion = VisformsAEF::getVersion(VisformsAEF::$customFieldTypeCalculation);
		$aefSubVersion = VisformsAEF::getVersion(VisformsAEF::$subscription);
		$hasBt4 = VisformsAEF::checkAEF(VisformsAEF::$bootStrap4Layouts);
		$hasUikit3 = VisformsAEF::checkAEF(VisformsAEF::$uikit3Layouts);
		$hasUikit2 = VisformsAEF::checkAEF(VisformsAEF::$uikit2Layouts);
		$task = $app->input->get('task');
		$numberpattern = '/^\-?\d+\.?\d*$/';

		if ($task != 'save2copy') {
			if (empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
				$data['editonlyfield'] = 0;
			}
			if (empty($this->aefList[VisformsAEF::$subscription])) {
				$data['customlabelforsummarypage'] = '';
				$data['customlabelformail'] = '';
				$data['customlabelforcsv']  = '';
				$data['fileexportformat']   = 0;
				$data['useassearchfieldonly'] = 0;
				$data['is_searchable'] = 0;
			}
			if (empty($this->aefList[VisformsAEF::$customFieldTypeLocation]) || $data['typefield'] !== 'location' || empty($data['frontdisplay'])) {
				$data['displayAsMapInList']   = 0;
				$data['displayAsMapInDetail'] = 0;
				$data['listMapHeight'] = 100;
				$data['listMapZoom'] = 8;
				$data['detailMapHeight'] = 400;
				$data['detailMapZoom'] = 13;
			}
			if($data['typefield'] === 'location') {
				if($data['frontdisplay'] == "2") {
					$data['displayAsMapInDetail'] = 0;
				}
				if($data['frontdisplay'] == "3") {
					$data['displayAsMapInList'] = 0;
				}
				if (empty($data['displayAsMapInDetail'])) {
					$data['listMapHeight'] = 100;
					$data['listMapZoom'] = 8;
				}
				if (empty($data['displayAsMapInList'])) {
					$data['detailMapHeight'] = 400;
					$data['detailMapZoom'] = 13;
				}
			}
			if (!empty($aefSubVersion) && version_compare($aefSubVersion, '3.1.0', 'lt')) {
				$data['useassearchfieldonly'] = 0;
				$data['allowferadiussearch'] = 0;
				$data['distanceunit'] = 'km';
			}
			if (!empty($aefSubVersion) && version_compare($aefSubVersion, '3.4.1', 'lt')) {
				$data['fileattachmentname'] = '';
			}
		}
		if (isset($data['defaultvalue']) && is_array($data['defaultvalue'])) {
			// check for bad sql statements in radiosql, selectsql or multicheckboxsql
			if (in_array($data['typefield'], array('selectsql', 'radiosql', 'multicheckboxsql'))) {
				$sql = $data['defaultvalue']['f_'.$data['typefield'].'_sql'];
				$sqlHelper = new VisformsSql($sql);
				if (!$sqlHelper->checkIsSelectSqlOnly()) {
					$app->enqueueMessage(JText::_("COM_VISFORMS_CREATE_SQL_BAD_SQL"), 'ERROR');
					return false;
				}
			}

            if ($task != 'save2copy') {
            	// ToDo Check if we can use show_label on uikit layouts?
	            if (empty($hasBt4) && empty($hasUikit2) && empty($hasUikit3)) {
		            unset($data['defaultvalue']['f_radio_show_label']);
		            unset($data['defaultvalue']['f_select_show_label']);
		            unset($data['defaultvalue']['f_multicheckbox_show_label']);
		            unset($data['defaultvalue']['f_file_show_label']);
		            unset($data['defaultvalue']['f_location_show_label']);
		            unset($data['defaultvalue']['f_calculation_show_label']);
		            unset($data['defaultvalue']['f_signature_show_label']);
	            }
                // check that fields of type calculation use only fields of "number"-types as placeholder
				$validCalculation = $this->checkCalculationString($data);
				if ($validCalculation === false) {
                    return false;
				}
				foreach ($this->allowedCalcualtionPlaceholderFieldTypes as $uncheckedValue) {
					if (empty($this->aefList[VisformsAEF::$customFieldTypeCalculation])) {
						unset($data['defaultvalue']["f_{$uncheckedValue}_unchecked_value"]);
					} else {
						if (isset($data['defaultvalue']["f_{$uncheckedValue}_unchecked_value"])) {
							$ucValue = trim(str_replace(",", ".", $data['defaultvalue']["f_{$uncheckedValue}_unchecked_value"]));
							if (!(preg_match($numberpattern, $ucValue) == true)) {
								$app->enqueueMessage(JText::sprintf('COM_VISFORMS_INVALID_UNCHECKED_VALUE'), 'error');
								return false;
							}
							$data['defaultvalue']["f_{$uncheckedValue}_unchecked_value"] = $ucValue;
						}
					}
					unset($uncheckedValue);
				}
                if (!$this->aefList[VisformsAEF::$subscription] && (empty($aefCalVersion) || (version_compare($aefCalVersion, '1.1.0', 'lt')))) {
                    unset($data['defaultvalue']['f_calculation_showWhen']);
				}
                if (!empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
                    // only allow to set aef field editonlyfield to 1 if field is not used as restrictor otherwise reset it to default
                    if (!$this->canSaveEditOnlyField($data)) {
                        $data['editonlyfield'] = 0;
                    }
                    // if aef editonlyfield is set to 1
                    if (!empty($data['editonlyfield'])) {
                        // remove restricts from submitted data
                        // restrictions in restrictor fields will be removed automatically by the code below and no new restrictions will be set if restricts are empty
                        $data['defaultvalue'] = VisformsConditionsHelper::removeRestrictsValues($data['defaultvalue'], $data['name'], false, false, array('_reload'));
                    }
                }
                // only allow to use either _showWhen or _reload
                if ($data['typefield'] == 'selectsql') {
                	// f_selectsql_render_as_datalist
	                if (!empty($data['defaultvalue']['f_selectsql_render_as_datalist'])) {
						unset($data['defaultvalue']['f_selectsql_attribute_required']);
		                unset($data['defaultvalue']['f_selectsql_attribute_multiselect']);
		                $data['defaultvalue']['f_selectsql_attribute_custominfo'] = "";
		                $data['defaultvalue']['f_selectsql_attribute_customerror'] = "";
		                $data['defaultvalue']['f_selectsql_attribute_size'] = "";
		                $data['defaultvalue']['f_selectsql_preSelectSolitaryOption'] = "";
	                }
	                if (!empty($data['defaultvalue']['f_selectsql_toggle_reload'])) {
		                // remove restricts from submitted data
		                // restrictions in restrictor fields will be removed automatically by the code below and no new restrictions will be set if restricts are empty
		                $data['defaultvalue']['f_selectsql_showWhen'] = "";
		                if (empty($data['defaultvalue']['f_selectsql_preSelectSolitaryOption'])) {
			                $data['defaultvalue']['f_selectsql_hideOnPreSelectedSolitaryOption']  = "";
		                }
	                }
	                else {
		                $data['defaultvalue']['f_selectsql_reload'] = "";
		                $data['defaultvalue']['f_selectsql_hideOnEmptyOptionList']  = "";
		                $data['defaultvalue']['f_selectsql_preSelectSolitaryOption']  = "";
		                $data['defaultvalue']['f_selectsql_hideOnPreSelectedSolitaryOption']  = "";
	                }
                }
                if (!empty($this->aefList[VisformsAEF::$customFieldTypeLocation]) && $data['typefield'] === 'location') {
					$zoom = (int) $data['defaultvalue']['f_location_zoom'];
					if (!$this->visformsValidate('min', array('count' => $zoom, 'mincount' => 1))
					|| !$this->visformsValidate('max', array('count' => $zoom, 'maxcount' => 20))
					|| !$this->visformsValidate('digits', array('value' => $zoom))) {
						$data['defaultvalue']['f_location_zoom'] = 13;
					} else {
						$data['defaultvalue']['f_location_zoom'] = $zoom;
					}
					$latCenter = $data['defaultvalue']['f_location_defaultMapCenter_lat'];
	                $lngCenter = $data['defaultvalue']['f_location_defaultMapCenter_lng'];
	                $latDefault = $data['defaultvalue']['f_location_attribute_value_lat'];
	                $lngDefault = $data['defaultvalue']['f_location_attribute_value_lng'];
	                $validLatDefault = $this->visformsValidate('latitude', array('value' => $latDefault)) || (empty($latDefault) && empty($lngDefault));
	                $validLngDefault = $this->visformsValidate('longitude', array('value' => $lngDefault)) || (empty($latDefault) && empty($lngDefault));
	                if ($latCenter === "" || $lngCenter === "" || !$this->visformsValidate('latitude', array('value' => $latCenter)) || !$this->visformsValidate('longitude',  array('value' => $lngCenter))) {
		                $app->enqueueMessage(JText::sprintf('COM_VISFORMS_LOCATION_DEFAULT_CENTER_VALUES_REQUIRED', ''), 'error');
		                return false;
	                }
	                if (!$validLatDefault || !$validLngDefault ) {
		                $app->enqueueMessage(JText::sprintf('COM_VISFORMS_LOCATION_DEFAULT_POSITION_VALUES_INVALID_FORMAT', ''), 'error');
		                return false;
	                }
                }
                if (empty($this->aefList[VisformsAEF::$subscription])) {
	                unset($data['defaultvalue']['f_date_mindate']);
	                unset($data['defaultvalue']['f_date_maxdate']);
	                unset($data['defaultvalue']['f_date_daydate_shift']);
	                unset($data['defaultvalue']['f_date_dynamic_min_shift']);
	                unset($data['defaultvalue']['f_date_dynamic_max_shift']);
	                unset($data['defaultvalue']['f_email_validate_mailExists']);
	                unset($data['defaultvalue']['f_select_customselectvaluetext']);
                }

                if ($data['typefield'] === "date" && $data['defaultvalue']['f_date_attribute_value'] === JFactory::getDbo()->getNullDate()) {
	                $data['defaultvalue']['f_date_attribute_value'] = "";
	            }
                
                // if we deal with a select, radio or multicheckbox and one of it's options,
                // that is used as a restriction in another field, is going to be removed, we have to do something
                // we will remove restricts in conditional field (defaultvalue) and give a message
	            if (!empty($data['id'])) {
		            if (in_array($data['typefield'], array('select', 'radio', 'multicheckbox'))) {
			            // get list of restritions and respective restricted field id's from database
			            $oldRestrictions = VisformsConditionsHelper::getRestrictions($data['id']);
			            if (isset($oldRestrictions['usedAsShowWhen']) && (count($oldRestrictions['usedAsShowWhen']) > 0)) {
				            $deletedOptionsIds = VisformsConditionsHelper::getRemovedOptionIds($data);
				            if (!empty($deletedOptionsIds)) {
					            // loop through restrictions
					            foreach ($oldRestrictions['usedAsShowWhen'] as $oRKey => $oRId) {
						            VisformsConditionsHelper::removeDeletedOptionsDependencies($oRKey, $oRId, $deletedOptionsIds, $data);
					            }
				            }
			            }
		            }
		            //remove old restrictions
		            VisformsConditionsHelper::removeRestriction(VisformsConditionsHelper::setRestrictsFromDb($data['id'], $data['fid']));

		            // fix unauthorized sql statements
		            if (!JFactory::getUser()->authorise('core.create.sql.statement', 'com_visforms') || empty($this->aefList[VisformsAEF::$subscription])) {
			            // if a user has no ACL to create sql statements, we just want to keep the old value!
			            if (in_array($data['typefield'], array('selectsql', 'radiosql', 'multicheckboxsqi'))) {
				            $oldDefaultvalues = VisformsHelper::registryArrayFromString(VisformsConditionsHelper::getDefaultValueFromDb($data['id']));
				            $data['defaultvalue']['f_'.$data['typefield'].'_sql'] = (isset($oldDefaultvalues['f_'.$data['typefield'].'_sql'])) ? $oldDefaultvalues['f_'.$data['typefield'].'_sql'] : "";
			            }
		            }
	            }

				//store a copy of defaultValues array in a variable, needed to save new restrictions after recordset is saved
                $restrictorDefaultValues = $data['defaultvalue'];
            }

            $data['defaultvalue'] = VisformsHelper::registryStringFromArray($data['defaultvalue']);
		}
		// ToDo uikit2 and uikit3?
		if (isset($data['gridSizes']) && is_array($data['gridSizes'])) {
			if ($task != 'save2copy') {
				// default field and label widths for some field types
				if (in_array($data['typefield'], array('submit', 'reset', 'image', 'pagebreak', 'hidden'))) {
					$data['gridSizes']['fieldsPerRow'] = 0;
					$data['gridSizes']['fieldsPerRowSm'] = 0;
					$data['gridSizes']['fieldsPerRowMd'] = 0;
					$data['gridSizes']['fieldsPerRowLg'] = 0;
					$data['gridSizes']['fieldsPerRowXl'] = 0;
					$data['gridSizes']['labelBootstrapWidth'] = 12;
					$data['gridSizes']['labelBootstrapWidthSm'] = 12;
					$data['gridSizes']['labelBootstrapWidthMd'] = 12;
					$data['gridSizes']['labelBootstrapWidthLg'] = 12;
					$data['gridSizes']['labelBootstrapWidthXl'] = 12;
				}
				$data['gridSizes'] = VisformsHelper::registryStringFromArray($data['gridSizes']);
			}
		}
		// alter the title for save as copy
		if ($task == 'save2copy') {
            list($label, $name) = $this->generateNewTitle('', $data['name'], $data['label']);
			$data['label']	= $label;
			$data['name']	= $name;
            $data['restrictions'] = "";
		}
		if (!empty($data['id']) ) {
			// get restrictions and stored field name, if a field is used in calculation
			$restrictions = VisformsConditionsHelper::getRestrictions($data['id']);
			if (!empty($restrictions) && ! empty($restrictions['usedInCal'])) {
				$oldFieldName = VisformsConditionsHelper::getOldFieldNameFromDb($data['id']);
			}
		}
		if (parent::save($data)) {
			$isNew = $this->getState($this->getName() . '.new');
			$newId = $this->getState($this->getName() . '.id');
			$restrictorId = (!empty($newId)) ? $newId : $data['id'];
			if ((!empty($isNew)) && (!empty($restrictorId)) && ($app->input->get('task') == 'save2copy')) {
                $oldRestricts = VisformsConditionsHelper::setRestrictsFromDb($restrictorId, $data['fid']);
				// only have to add new restrictions to existing fields
				if (!empty($oldRestricts)) {
					VisformsConditionsHelper::setRestriction($oldRestricts);
				}
			}
			else {
				// save restrictions
				VisformsConditionsHelper::setRestriction(VisformsConditionsHelper::setRestricts($restrictorId, $restrictorDefaultValues, $data['name'], $data['fid']));
				// fix modified field name of fields used in calculation
				if (!empty($oldFieldName) && $oldFieldName != $data['name']) {
					foreach ($restrictions['usedInCal'] as $calFieldId) {
						VisformsConditionsHelper::fixModifiedFieldNameInCalculation($oldFieldName, $data['name'], $calFieldId);
					}
				}
			}
			return true;
		}
		else {
			// error case, save was not successful
			if (($app->input->get('task') != 'save2copy') && !empty($data['id'])) {
				// restrictions were deleted previously, the must be reset
				VisformsConditionsHelper::setRestriction(VisformsConditionsHelper::setRestrictsFromDb($data['id'], $data['fid']));
			}
			return false;
		}
	}
	
	/**
     * Method to create a field in datatable
	 * test if data must be saved in DB for this form
	 * @params string $fid form id
	 * @return boolean true
	 *
	 * @since Joomla 1.6
	 */
	public function createDataTableFields($fid, $id, $saveresult) {
        if (!$this->createDataTableField($fid, $id, $saveresult)) {
            //throw error
        }
        if (!$this->createDataTableField($fid, $id, $saveresult, true)) {
             //throw error
        }
	    return true;
	}

	public function getForm($data = array(), $loadData = true) {
		// get the form
		$form = $this->loadForm('com_visforms.visfield', 'visfield', array('control' => 'jform', 'load_data' => $loadData));
		$aefCalVersion = VisformsAEF::getVersion(VisformsAEF::$customFieldTypeCalculation);
		$aefSubVersion = VisformsAEF::getVersion(VisformsAEF::$subscription);
		$hasBt4 = VisformsAEF::checkAEF(VisformsAEF::$bootStrap4Layouts);
		$hasUikit2 = VisformsAEF::checkAEF(VisformsAEF::$uikit2Layouts);
		$hasUikit3 = VisformsAEF::checkAEF(VisformsAEF::$uikit3Layouts);
		if (empty($form)) {
			return false;
		}
		$app = \JFactory::getApplication();
		$fid = $app->input->getInt('fid', isset($data['fid']) ? $data['fid'] : 0);
		$id = $app->input->getInt('id', 0);
		if ($fid != 0) {
			$model = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
			$visform = $model->getItem($fid);
			if ((!empty($visform)) && ($visform->layoutsettings['formlayout'] != 'btdefault') && ($visform->layoutsettings['formlayout'] != 'bt3default') && ($visform->layoutsettings['displaysublayout'] != 'individual')) {
				$form->removeField('bootstrap_size');
				$form->removeField('multicolumnbtdesc');
			}
			if ((empty($hasBt4) && (empty($hasUikit3)) && (empty($hasUikit2))) || (!empty($visform) && (!in_array($visform->layoutsettings['formlayout'], array( 'bt4mcindividual', 'uikit3', 'uikit2'))))) {
				$form->removeField('f_radio_show_label', 'defaultvalue');
				$form->removeField('f_select_show_label', 'defaultvalue');
				$form->removeField('f_multicheckbox_show_label', 'defaultvalue');
				$form->removeField('f_file_show_label', 'defaultvalue');
				$form->removeField('f_calculation_show_label', 'defaultvalue');
				$form->removeField('f_location_show_label', 'defaultvalue');
				$form->removeField('f_signature_show_label', 'defaultvalue');
				$form->removeField('cssClassesDesc');
				$form->removeField('controlGroupCSSclass');
				$form->removeGroup('gridSizes');
			}
			if ((empty($hasBt4) && (empty($hasUikit3)) && (empty($hasUikit2))) || ((!empty($visform) && (!isset($visform->layoutsettings['displaysublayout']) || ($visform->layoutsettings['displaysublayout'] != 'individual'))))) {
				$form->removeGroup('gridSizes');
			}
			if (!empty($visform)  && $visform->layoutsettings['formlayout'] == 'uikit2' && isset($visform->layoutsettings['displaysublayout']) && $visform->layoutsettings['displaysublayout'] == 'individual') {
				$form->removeField('fieldsPerRowXl', 'gridSizes');
				$form->removeField('labelBootstrapWidthXl', 'gridSizes');
			}
		}
		$user = JFactory::getUser();

		// check for existing article
		// modify the form based on Edit State access controls
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_visforms.visform.'. $fid . '.visfield.'.(int) $id))
		    || ($id == 0 && !$user->authorise('core.edit.state', 'com_visforms.visform.'. $fid))) {
			// disable fields for display
			$form->setFieldAttribute('published', 'disabled', 'true');
		}
        $form->setFieldAttribute('ordering', 'disabled', 'true');
        // remove aef fields
        if (empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
            $form->removeField('editonlyfield');
        }
        if (empty($this->aefList[VisformsAEF::$subscription])) {
        	if (empty($aefCalVersion) || (version_compare($aefCalVersion, '1.1.0', 'lt'))) {
		        $form->removeField('f_calculation_showWhen', 'defaultvalue');
	        }
	        $form->removeField('customlabelforsummarypage');
	        $form->removeField('customlabelformail');
	        $form->removeField('customlabelforcsv');
	        $form->removeField('fileexportformat');
	        $form->removeField('useassearchfieldonly');

	        $form->removeField('f_date_mindate', 'defaultvalue');
	        $form->removeField('f_date_maxdate', 'defaultvalue');
	        $form->removeField('f_date_daydate_shift', 'defaultvalue');
	        $form->removeField('f_date_dynamic_min_shift', 'defaultvalue');
	        $form->removeField('f_date_dynamic_max_shift', 'defaultvalue');
	        $form->removeField('f_email_validate_mailExists', 'defaultvalue');
	        $form->removeField('f_select_customselectvaluetext', 'defaultvalue');
	        $form->removeField('f_select_is_searchable', 'defaultvalue');
	        $form->setFieldAttribute('f_date_minvalidation_type', 'label', JText::_('COM_VISFORMS_MIN_DATE_VALIDATION_TYPE'). JText::_('COM_VISFORMS_SUBSCRIPTION_ONLY'), 'defaultvalue');
	        $form->setFieldAttribute('f_date_maxvalidation_type', 'label', JText::_('COM_VISFORMS_MAX_DATE_VALIDATION_TYPE'). JText::_('COM_VISFORMS_SUBSCRIPTION_ONLY'), 'defaultvalue');
		}
		if (empty($this->aefList[VisformsAEF::$customFieldTypeLocation])) {
			$form->removeField('displayAsMapInList');
			$form->removeField('displayAsMapInDetail');
			$form->removeField('listMapHeight');
			$form->removeField('listMapZoom');
			$form->removeField('detailMapHeight');
			$form->removeField('detailMapZoom');
		}
		if (!empty($aefSubVersion) && version_compare($aefSubVersion, '3.1.0', 'lt')) {
			$form->removeField('useassearchfieldonly');
			$form->removeField('allowferadiussearch');
			$form->removeField('distanceunit');
		}
		if (!empty($aefSubVersion) && version_compare($aefSubVersion, '3.4.1', 'lt')) {
			$form->removeField('fileattachmentname');
		}
		if (!$user->authorise('core.create.sql.statement', 'com_visforms')) {
			$form->setFieldAttribute('f_selectsql_sql', 'disabled', 'true', 'defaultvalue');
			$form->setFieldAttribute('f_radiosql_sql', 'disabled', 'true', 'defaultvalue');
			$form->setFieldAttribute('f_multicheckboxsql_sql', 'disabled', 'true', 'defaultvalue');
		}
		return $form;
	}

    protected function loadFormData() {
		// check the session for previously entered form data
		$data = JFactory::getApplication()->getUserState('com_visforms.edit.visfield.data', array());
		if (empty($data)) {
            $data = $this->getItem();
		}
		return $data;
	}

    protected function loadFormFieldsParameters() {
        $item = $this->item;
        $item->defaultvalue = VisformsHelper::registryArrayFromString($item->defaultvalue);
        $item->restrictions = VisformsHelper::registryArrayFromString($item->restrictions);
		$item->gridSizes = VisformsHelper::registryArrayFromString($item->gridSizes);
    }

    protected function getReorderConditions($table) {
	    return 'fid = '.JFactory::getApplication()->input->get('fid', 0);
	}
	
	protected function canEditState($record) {
		$user = JFactory::getUser();
		// check for existing field
		if (!empty($record->id)  && !empty($record->fid)) {
            return $user->authorise('core.edit.state', 'com_visforms.visform.' . (int) $record->fid . '.visfield.' .(int) $record->id);
		}
        else {
            // default to component settings
            return parent::canEditState($record);
		}
	}
	
    protected function canDelete($record) {
		if (!empty($record->id)  && !empty($record->fid)) {
			$canDelete = VisformsConditionsHelper::canDelete($record->id, $record->name);
			if (empty($canDelete)) {
				return false;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_visforms.visform.' . (int) $record->fid . '.visfield.' .(int) $record->id);
		}
		else {
			// use component settings
			return parent::canDelete($record);
		}
	}
    
    protected function canSaveEditOnlyField($data) {
        if (!empty($data['id'])) {
            if (empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
                return true;            
            }
            if (empty($data['editonlyfield'])) {
                return true;            
            }
	        //db field restrictions is not part of the data array, therefore we pass id and name
	        $canSaveEditOnlyField = VisformsConditionsHelper::canSaveEditOnlyField($data['id'], $data['name']);
	        if (empty($canSaveEditOnlyField)) {
		        return false;
	        }
		}
        return true;
    }
	
	protected function generateNewTitle( $catid, $name, $label) {
		// alter the label & name
		$table = $this->getTable();
		while ($table->load(array('name' => $name))) {
			$label = StringHelper::increment($label);
			$name = StringHelper::increment($name, 'dash');
		}
		return array($label, $name);
	}

	/**
	 * Method to assure creation of data table fileds for already loaded field model
	 * used by controller::postSaveHook() and others
	 * @params no
	 * @return void
	 * @since Joomla 1.6
	 */
	public function assureCreateDataTableFields() {
		$item = $this->getItem();
		$id = $item->get('id');
		$fid = $item->get('fid');
		if ($fid && $id) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->qn('#__visforms'))
				->where($db->qn('id') . ' = ' . $fid);
			//Add field to data tables
			$db->setQuery($query);
			$forms = $db->loadObjectList();
			if (count($forms ) > 0) {
				$this->createDataTableFields($fid, $id, $forms[0]->saveresult);
			}
		}
	}

    /**
     * Method to create a new field in the data table (used for storing submitted user inputs)
     * @param int $fid form id
     * @param int $id field id
     * @param boolean $save set to true if field is to be created in the data save table
     * @return boolean
     */
    public function createDataTableField($fid, $id, $saveresult, $save = false) {
	    $dba	= JFactory::getDbo();
    	$tn = "#__visforms_".$fid;
	    $tnFull = strtolower($dba->getPrefix(). 'visforms_'.$fid);
        if ($save === true) {
            $tn .= "_save";
	        $tnFull .= "_save";
        }

	    $tablesAllowed = $dba->getTableList();
	    if (!empty($tablesAllowed)) {
		    $tablesAllowed = array_map('strtolower', $tablesAllowed);
	    }

	    if (!in_array($tnFull, $tablesAllowed) && !$saveresult) {
		    return true;
	    }
		$tableFields = $dba->getTableColumns($tn,false);
		$fieldName = "F" . $id;
		if (!isset( $tableFields[$fieldName] )) {
            $query = "ALTER TABLE ".$dba->qn($tn)." ADD ".$dba->qn($fieldName)." TEXT NULL";
			$dba->setQuery($query);
			if (!$dba->execute()) {
                JFactory::getApplication()->enqueueMessage( JText::_( 'COM_VISFORMS_PROBLEM_WITH' )." (".$query.")", 'warning');
				return false;
			}
		    return true;
		}
	    return true;
	}
    
    /**
     * Method to publish a recordset
     * @param array $pks array of id's
     * @param boolean $value wether to publish or unpublish
     * @return type
     */
    public function publish(&$pks, $value = 1) {
		$pks = (array) $pks;
		// look for restrictions
		foreach ($pks as $i => $pk) {
            $restrictions = VisformsConditionsHelper::getRestrictions($pk);
            if ((is_array($restrictions)) && (count($restrictions) > 0) && isset($this->task) && $this->task == 'unpublish') {
                // give an error message
                JFactory::getApplication()->enqueueMessage(JText::sprintf("COM_VISFORMS_FIELD_HAS_RESTICTIONS", $pk), 'warning');
                // unset the pk
                unset($pks[$i]); 
            }
        }
        return parent::publish($pks, $value);
    }

	protected function checkCalculationString($data) {
		if ($data['typefield'] != 'calculation') {
			return true;
		}
		$equation = $data['defaultvalue']['f_calculation_equation'];
		if (empty($equation)) {
			return true;
		}
		$pattern = '/\[[A-Z0-9]{1}[A-Z0-9\-]*]/';
		if (preg_match_all($pattern, $equation, $matches)) {
			$fid = $data['fid'];
			$allowedFieldTypes = implode('","', $this->allowedCalcualtionPlaceholderFieldTypes);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->qn('name'))
				->from($db->qn('#__visfields'))
				->where($db->qn('typefield') . ' in ("' . $allowedFieldTypes .'")')
				->where($db->qn('fid') . " = " . $fid)
				->where($db->qn('published') . " = " . 1);
			try {
				$db->setQuery($query);
                $fields = $db->loadColumn();
			}
			catch (runtimeExeption $e) { }
            if (empty($fields)) {
                return true;
			}
			// found matches are store in the $matches[0] array
			foreach ($matches[0] as $match) {
                $str = trim($match, '\[]');
				$fieldName = StringHelper::strtolower($str);
				if (!(in_array($fieldName, $fields))) {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_INVALID_PLACEHOLDER_TYPE_IN_CALCULATION', $match), 'error');
					return false;
				}
			}
		}
		return true;
	}

	protected function visformsValidate($type, $arg) {
    	return VisformsValidate::validate($type, $arg);
	}

	public function saveorderDataDetail($pks, $order) {
		$this->initBatch();
		$this->table->setColumnAlias('ordering', 'dataordering');
		$return = parent::saveorder($pks, $order);
		$this->table->setColumnAlias('ordering', 'ordering');
		return $return;
	}
}