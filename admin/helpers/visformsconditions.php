<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsConditionsHelper
{

	protected static $restrictRestrictionMap = array(
		'_validate_equalTo' => 'usedAsEqualTo',
		'_minvalidation_type' => 'usedAsMinDate',
		'_maxvalidation_type' => 'usedAsMaxdate',
		'_showWhen' => 'usedAsShowWhen',
		'_equation' => 'usedInCal',
		'_reload' => 'usedAsReloadTrigger');

	protected static $restrictionTypeNameMap = array(
		'usedAsEqualTo' => 'COM_VISFORMS_EQUAL_TO',
		'usedAsMinDate' => 'COM_VISFORMS_MIN_DATE_VALIDATION_TYPE',
		'usedAsMaxdate' => 'COM_VISFORMS_MAX_DATE_VALIDATION_TYPE',
		'usedAsShowWhen' => 'COM_VISFORMS_SHOW_WHEN',
		'usedInCal' => 'COM_VISFORMS_CALCULATION_EQUATION',
		'usedAsReloadTrigger' => 'COM_VISFORMS_USED_AS_RELOAD_TRIGGER');

	public static function getDefaultValueFromDb($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('defaultvalue')))
			->from('#__visfields')
			->where('id = ' . $id);
		try {
			$db->setQuery($query);
			$result = $db->loadResult();
			if (!empty($result)) {
				return $result;
			}
		}
		catch (RuntimeException $e) {

		}
		return '';
	}

	// note: also used in importExportHelper
	public static function saveDefaultValue($id, $value) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->update($db->quoteName('#__visfields'))
			->set($db->quoteName('defaultvalue') . " = " . $db->quote($value))
			->where($db->quoteName('id') . " = " . $id);
		$db->setQuery($query);
		$db->execute();
	}

	public static function getOldFieldNameFromDb($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('name')))
			->from('#__visfields')
			->where('id = ' . $id);
		try {
			$db->setQuery($query);
			$result = $db->loadResult();
			if (!empty($result)) {
				return $result;
			}
		}
		catch (RuntimeException $e) {

		}
		return '';
	}

	public static function fixModifiedFieldNameInCalculation($oldFieldName, $newFieldName, $calFieldId) {
		$defaultvalue = self::getDefaultValueFromDb($calFieldId);
		$pattern = '/\['.strtoupper($oldFieldName).']/';
		$defaultvalue = preg_replace($pattern, '['.strtoupper($newFieldName).']', $defaultvalue);
		self::saveDefaultValue($calFieldId, $defaultvalue);
	}

	public static function getRestrictions($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// select restriction
		$query
			->select('restrictions')
			->from('#__visfields')
			->where('id = ' . $id);
		$db->setQuery($query);
		$result = $db->loadResult();
		if (empty($result)) {
			return array();
		}
		$restrictions = VisformsHelper::registryArrayFromString($result);

		return $restrictions;
	}

	public static function saveRestriction($id, $value) {
		$db = JFactory::getDbo();
		$db->setQuery("UPDATE " . $db->quoteName('#__visfields') . " SET " . $db->quoteName('restrictions') . " = " . $db->quote($value) . " WHERE " . $db->quoteName('id') . " = " . $id);
		$db->execute();
	}

	public static function setRestrictsFromDb($id, $fid = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('defaultvalue', 'name')))
			->from('#__visfields')
			->where('id = ' . $id);

		$db->setQuery($query);
		$result = $db->loadObject();
		if (empty($result)) {
			return array();
		}
		// convert the default value field to an array
		$defaultValues = VisformsHelper::registryArrayFromString($result->defaultvalue);
		$name = $result->name;
		return self::setRestricts($id, $defaultValues, $name, $fid);
	}

	public static function setRestricts($id, $defaultValues = array(), $name = '', $fid = null) {
		$restricts = array();
		foreach ($defaultValues as $dfName => $dfValue) {
			foreach (self::$restrictRestrictionMap as $rName => $rValue) {
				if ((strpos($dfName, $rName) > 0)) {
					$className = 'VisformsVisfieldRestrict' . ucfirst(self::$restrictRestrictionMap[$rName]);
					if (!class_exists($className)) {
						JLoader::register($className, JPATH_ADMINISTRATOR . '/components/com_visforms/lib/visfieldrestrict/' . strtolower(self::$restrictRestrictionMap[$rName] . '.php'));
					}
					$list = JLoader::getClassList();
					if (class_exists($className)) {
						$o = new $className($dfValue, $id, $name, $fid);
						$newRestricts = $o->getRestricts();
						if (!empty($newRestricts) && is_array($newRestricts)) {
							foreach ($newRestricts as $newRestrict) {
								array_push($restricts, $newRestrict);
							}
						}

					}
				}
			}
		}
		return $restricts;
	}

	public static function removeRestriction($restricts) {
		while (!empty($restricts)) {
			// pop the first ID off the stack
			$deletedRestriction = array_shift($restricts);
			// extract params in database field restrictions
			$restrictions = self::getRestrictions($deletedRestriction['restrictedId']);
			// if deletedRestriction is set, remove it
			foreach ($restrictions as $r => $v) {
				if ($r == $deletedRestriction['type']) {
					foreach ($v as $index => $restrictorId) {
						if ($restrictorId == $deletedRestriction['restrictorId']) {
							unset($restrictions[$r][$index]);
						}
					}
				}
			}
			foreach ($restrictions as $r => $v) {
				if ((is_array($v)) && (count($v) == 0)) {
					unset($restrictions[$r]);
				}
			}

			if (isset($restrictions) && is_array($restrictions)) {
				$restrictions = VisformsHelper::registryStringFromArray($restrictions);
			}
			// save the changed deletedRestriction
			self::saveRestriction($deletedRestriction['restrictedId'], $restrictions);
		}
		return true;
	}

	public static function setRestriction($restricts) {
		while (!empty($restricts)) {
			// pop the first ID off the stack
			$newRestriction = array_shift($restricts);
			// extract params in database field restrictions
			$restrictions = self::getRestrictions($newRestriction['restrictedId']);
			//check if newRestriction type already exists, if not, create it as array
			if (!array_key_exists($newRestriction['type'], $restrictions)) {
				$restrictions[$newRestriction['type']] = array();
			}
			// add newRestriction of this type to restrictions of field, if the newRestriction already exists it is just overriden with the same value
			$restrictions[$newRestriction['type']][$newRestriction['restrictorName']] = $newRestriction['restrictorId'];
			if (isset($restrictions) && is_array($restrictions)) {
				$restrictions = VisformsHelper::registryStringFromArray($restrictions);
			}
			self::saveRestriction($newRestriction['restrictedId'], $restrictions);
		}
		return true;
	}

	public static function getRemovedOptionIds($data) {
		$oldDefaultValues = VisformsHelper::registryArrayFromString(self::getDefaultValueFromDb($data['id']));
		$oldOptions = $oldDefaultValues['f_' . $data['typefield'] . '_list_hidden'];
		$options = $data['defaultvalue']['f_' . $data['typefield'] . '_list_hidden'];
		if ($oldOptions === $options) {
			//options have not been changed
			return false;
		}
		if (empty($oldOptions)) {
			$oldOptions = array();
		}
		else {
			$oldOptions = VisformsHelper::registryArrayFromString($oldOptions);
		}
		if (empty($options)) {
			$options = array();
		}
		else {
			$options = VisformsHelper::registryArrayFromString($options);
		}
		$oldOptionsIds = array_map(function ($element) {
			return $element['listitemid'];
		}, $oldOptions);
		$optionsIds = array_map(function ($element) {
			return $element['listitemid'];
		}, $options);
		return $removedOptionsIds = array_values(array_diff($oldOptionsIds, $optionsIds));
	}

	//$data[id] = id of select, radio, multieelect from which an option was removed
	//$id = id of field which is listed in the restrictions of thie select, radio, mulitcheckbox and which may have a condition that uses the deleted option
	public static function removeDeletedOptionsDependencies($fieldName, $id, $deletedOptionsId, $data) {
		//try to run not to much code
		$restrictedId = $data['id'];
		$name = $data['name'];
		$oldDefaultValues = self::getDefaultValueFromDb($id);
		$oldDefaultValuesArray = VisformsHelper::registryArrayFromString($oldDefaultValues);
		$usedRemovedShowWhens = array();
		$usedRemovedOptionsIds = array();
		//simple check, if removed option is used as condition in this specific field
		foreach ($deletedOptionsId as $optionId) {
			$search = '"field' . $restrictedId . '__' . $optionId . '"';
			if (strpos($oldDefaultValues, $search) === false) {
				continue;
			}
			//removed options was used as condition, store information, so that we can sanitize db with as little effort as possible
			$usedRemovedShowWhens[] = 'field' . $restrictedId . '__' . $optionId;
			$usedRemovedOptionsIds[] = $optionId;
		}
		//only if option was used, sanitize conditional field and restrictions.
		if (!empty($usedRemovedShowWhens)) {
			$removeRestriction = true;
			//stanitize default vaules of conditional field and store them in db
			$showWhenValues = array();
			foreach ($oldDefaultValuesArray as $key => $value) {
				if (strpos($key, '_showWhen') !== false) {
					$showWhenValues = $value;
					break;
				}
			}
			//remove deleted showWhen values from showWhenValues Array and find out, if conditional field can be removed from restrition in select, radio, multiselect
			$newShowWhenValues = array_diff($showWhenValues, $usedRemovedShowWhens);
			if (!empty($newShowWhenValues)) {
				//check if all showWhenValues for the modified select, radio, multicheckbox have been removed
				foreach ($newShowWhenValues as $keyName => $keyValue) {
					if (strpos($keyValue, 'field' . $restrictedId . '__') !== false) {
						$removeRestriction = false;
						break;
					}
				}
				$oldDefaultValuesArray[$key] = $newShowWhenValues;
			}
			else {
				unset($oldDefaultValuesArray[$key]);
				$removeRestriction = true;
			}
			$newDefaultValues = VisformsHelper::registryStringFromArray($oldDefaultValuesArray);
			self::saveDefaultValue($id, $newDefaultValues);
			//sanitize restrictions of (select, radio, multicheckbox field) and store them in db
			if ($removeRestriction === true) {
				$restrictions = self::getRestrictions($restrictedId);
				$oldUsedAsShowWhenRestrition = (!empty($restrictions['usedAsShowWhen'])) ? $restrictions['usedAsShowWhen'] : array();
				if (!empty($oldUsedAsShowWhenRestrition)) {
					unset($oldUsedAsShowWhenRestrition[$fieldName]);
				}
				if (!empty($oldUsedAsShowWhenRestrition)) {
					$restrictions['usedAsShowWhen'] = $oldUsedAsShowWhenRestrition;
				}
				else {
					unset($restrictions['usedAsShowWhen']);
				}
				if (!empty($restrictions)) {
					$restrictions = VisformsHelper::registryStringFromArray($restrictions);
				}
				else {
					$restrictions = '';
				}
				self::saveRestriction($restrictedId, $restrictions);
			}
			//set a message
			JFactory::getApplication()->enqueueMessage(JText::sprintf("COM_VISFORMS_OPTION_TOGGLES_DISPLAY", $name, $fieldName), 'notice');
		}
	}

	public static function canDelete($id, $name) {
		$restrictions = self::getRestrictions($id);
		if (!(empty($restrictions))) {
			foreach ($restrictions as $r => $value) {
				foreach ($value as $fieldName => $fieldId) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_HAS_RESTRICTIONS', $name, JText::_(self::$restrictionTypeNameMap[$r]), $fieldName), 'warning');
				}
			}
			return false;
		}
		return true;
	}

	public static function canSaveEditOnlyField($id, $name) {
		$restrictions = self::getRestrictions($id);
		if (!(empty($restrictions))) {
			foreach ($restrictions as $r => $value) {
				foreach ($value as $fieldName => $fieldId) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_HAS_SAVE_RESTRICTIONS', $name, JText::_(self::$restrictionTypeNameMap[$r]), $fieldName, JText::_('COM_VISFORMS_IS_EDIT_ONLY_FIELD_LABEL')), 'warning');
				}
			}
			return false;
		}
		return true;
	}

	public static function setConditionsInCopiedFields($idMap, $fid) {
		foreach ($idMap as $newId) {
			// get restrictions in new Field
			$oldRestricts = self::setRestrictsFromDb($newId, $fid);
			$newEqualToRestrict = "";
			$newMinDateRestrict = "";
			$newMaxDateRestrict = "";
			$newShowWhenRestricts = array();
			$newReloadTrigger = array();
			if (!empty($oldRestricts)) {
				//replace oldIds in restricts['retrictedId'] with the proper newId value
				$c = count($oldRestricts);
				for ($i = 0; $i < $c; $i++) {
					$oldRestrictId = $oldRestricts[$i]['restrictedId'];
					if (!array_key_exists($oldRestrictId, $idMap)) {
						//used in calculation condition
						continue;
					}
					//replace restrictedId in restrict
					$oldRestricts[$i]['restrictedId'] = $idMap[$oldRestrictId];

					//collect information to sanitize restricts in new fields
					switch ($oldRestricts[$i]['type']) {
						case 'usedAsEqualTo' :
							$newEqualToRestrict = '#field' . $oldRestricts[$i]['restrictedId'];
							break;
						case 'usedAsMinDate' :
							$newMinDateRestrict = '#field' . $oldRestricts[$i]['restrictedId'];
							break;
						case 'usedAsMaxDate' :
							$newMaxDateRestrict = '#field' . $oldRestricts[$i]['restrictedId'];
							break;
						case 'usedInCal' :
							//cal restricts is the calculation equation. we copy form with fields.
							// fieldnames are not changes ==> equation uses fieldnames and is therefore still valide ==> nothing to change in defaultvalue
							break;
						case 'usedAsShowWhen' :
							$newShowWhenRestricts[] = $oldRestricts[$i];
							break;
						case 'usedAsReloadTrigger' :
							$newReloadTrigger[] = 'field' . $oldRestricts[$i]['restrictedId'];
							break;
						default :
							//actually these data are invalid, we prevent them from being stored in the defaultvalue again and add a message
							unset ($oldRestricts{$i});
							JFactory::getApplication()->enqueueMessage((JText::_('COM_VISFORMS_CHECK_RESTRICTS_AFTER_BATCH_COPY')));
							break;
					}
				}
				$oldRestricts = array_values($oldRestricts);
			}

			// set and save restrictions
			self::setRestriction($oldRestricts);
			// save new showWhenrestricts
			// create strings from newShowWhenRestrics
			$newShowWhenRestricts = array_map(function ($element) {
				return 'field' . $element['restrictedId'] . '__' . $element['optionId'];
			}, $newShowWhenRestricts);
			// get old values from database
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('defaultvalue', 'typefield')))
				->from($db->quoteName('#__visfields'))
				->where('id = ' . $newId);

			$db->setQuery($query);
			$result = $db->loadObject();

			// extract default value
			$defaultValue = VisformsHelper::registryArrayFromString($result->defaultvalue);

			// reset or remove value in default value
			if (!empty($newShowWhenRestricts)) {
				$defaultValue['f_' . $result->typefield . '_showWhen'] = $newShowWhenRestricts;
			}
			else {
				unset($defaultValue['f_' . $result->typefield . '_showWhen']);
			}
			if (!empty($newEqualToRestrict)) {
				$defaultValue['f_' . $result->typefield . '_validate_equalTo'] = $newEqualToRestrict;
			}
			else {
				$defaultValue['f_' . $result->typefield . '_validate_equalTo'] = "0";
			}
			if (!empty($newMinDateRestrict)) {
				$defaultValue['f_' . $result->typefield . '_minvalidation_type'] = $newMinDateRestrict;
			}
			else {
				$defaultValue['f_' . $result->typefield . '_minvalidation_type'] = "";
			}
			if (!empty($newMaxDateRestrict)) {
				$defaultValue['f_' . $result->typefield . '_maxvalidation_type'] = $newMaxDateRestrict;
			}
			else {
				$defaultValue['f_' . $result->typefield . '_maxvalidation_type'] = "";
			}
			if (!empty($newReloadTrigger)) {
				$defaultValue['f_' . $result->typefield . '_reload'] = $newReloadTrigger;
			}
			else {
				$defaultValue['f_' . $result->typefield . '_reload'] = "";
			}

			// parse default value as string
			$defaultValue = VisformsHelper::registryStringFromArray($defaultValue);
			// update database
			self::saveDefaultValue($newId, $defaultValue);
		}
	}

	public static function removeRestrictsValues($defaultValue, $fieldName, $msg = true, $register = true, $excludes = array()) {
		if (!empty($register)) {
			$defaultValue = VisformsHelper::registryArrayFromString($defaultValue);
		}

		foreach ($defaultValue as $dfName => $dfValue) {
			if ((strpos($dfName, '_validate_equalTo') > 0) && (strpos($dfValue, '#field') === 0)) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_EQUAL_TO'), $fieldName), 'warning');
				}
			}
			if ((strpos($dfName, '_minvalidation_type') > 0) && (strpos($dfValue, '#field') === 0)) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_MIN_DATE_VALIDATION_TYPE'), $fieldName), 'warning');
				}
			}
			if ((strpos($dfName, '_maxvalidation_type') > 0) && (strpos($dfValue, '#field') === 0)) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_MAX_DATE_VALIDATION_TYPE'), $fieldName), 'warning');
				}
			}
			if ((strpos($dfName, '_showWhen') > 0) && is_array($dfValue)) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_SHOW_WHEN'), $fieldName), 'warning');
				}
			}
			// clear value in equation of field of type calculation
			if ((strpos($dfName, '_equation') > 0) && (!empty($dfValue))) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_FIELD_CALCULATION'), $fieldName), 'warning');
				}
			}
			if (!in_array('_reload', $excludes) && (strpos($dfName, '_reload') > 0) && is_array($dfValue)) {
				$defaultValue[$dfName] = '';
				if ($msg) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_RELOAD_WHEN'), $fieldName), 'warning');
				}
			}
		}
		if (isset($defaultValue) && is_array($defaultValue) && (!empty($register))) {
			$defaultValue = VisformsHelper::registryStringFromArray($defaultValue);
		}
		return $defaultValue;
	}
}