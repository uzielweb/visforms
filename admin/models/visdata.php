<?php

/**
 * Visdata model for visforms
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
require_once (JPATH_ADMINISTRATOR . '/components/com_visforms/models/visdatas.php');

class VisformsModelVisdata extends VisFormsItemModelBase
{
    protected $fieldDefinition;
    
    public function __construct($config = array()) {
	    $config['event_after_save'] = 'onVisformsdataAfterJFormSave';
	    $config['event_after_delete'] = 'onVisformsdataAfterJFormDelete';
	    $config['event_change_state'] = 'onVisformsdataJFormChangeState';
        parent::__construct($config);
        $this->fieldDefinition = $this->getDatafields();
	    JPluginHelper::importPlugin('visforms');
    }

    public function getDatafields() {
        $model = JModelLegacy::getInstance('Visdatas', 'VisformsModel', array('ignore_request' => true));
        $fieldDefinition = $model->getDatafields('published = 1');
        if (!empty($fieldDefinition)) {
            $count = count($fieldDefinition);
            for ($i = 0; $i < $count; $i++) {
                $fieldDefinition[$i] = $this->extractDefaultValueParams($fieldDefinition[$i]);
            }
            return $fieldDefinition;
        }
        return false;
    }

    public function getForm($data = array(), $loadData = false) {
		// get the form
		$form = $this->loadForm('com_visforms.visdata', 'visdata', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
	    if ($this->canDoTask('core.change.data.record.user')) {
		    $form->setFieldAttribute('created_by', 'readonly', false);
	    }
        $fields = $this->fieldDefinition;
        foreach ($fields as $field) {
            $required = '';//we cannot add required validation without proper handling of conditional fields!
            switch($field->typefield) {
                case 'text':
                case 'password':
                case 'hidden':
                case 'calculation':
	            $fieldString = '<field name="F'. $field->id. '"'.
		            ' type="text"'.
		            ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
		            $required .
		            ' />';
	            $fieldXml = new SimpleXMLElement($fieldString);
	            $form->setField($fieldXml);
	            break;
	            case 'location':
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="text"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
	                    ' class="inputbox input-xxlarge"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    break;
                case 'file':
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="file"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        ' class="hiddenFileUpload"' .
                        ' disabled="true"' .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    break;
                case 'email' :
                case 'checkbox':
                case 'number':
                case 'url':
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="'.$field->typefield.'"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    break;
                case 'textarea':
                    $type = (!empty($field->HTMLEditor)) ? 'editor' : 'textarea';
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    unset($type);
                    break;
                case 'select':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    $selectOptions .= '<option value="">'.htmlspecialchars(JText::_('CHOOSE_A_VALUE'),ENT_COMPAT, 'UTF-8').'</option>';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option value="'.htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8').'">'.htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8').'</option>';
                    }
                    $type = 'list';
                    $multiple = (!empty($field->attribute_multiple)) ? ' multiple="true"' : '' ;
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        $multiple .
                        $required .
                        '>'.
                        $selectOptions .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    unset($type);
                    unset($multiple);
                    break;
                case 'radio':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option value="' . htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8') . '">'. htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8') .'</option>';
                    }
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="'.$field->typefield.'"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        ' class="radio inline"' .
                        '>'.
                        $selectOptions .
                        $required .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    break;
                case 'multicheckbox':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option class="checkbox inline" value="'.htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8').'">'.htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8').'</option>';
                    }
                    $type = 'checkboxes';
                    $maxLength = ((!empty($field->attribute_maxlength)) && ($field->attribute_maxlength > 1)) ? ' maxlength="'.$field->attribute_maxlength.'"' : '' ;
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        $required .
                        $maxLength .
                        '>'.
                        $selectOptions .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    unset($type);
                    unset($maxLength);
                    break;
                case 'date':
                    $dateFormat = '';
                    $format = (!empty($field->format)) ? explode(';', $field->format) : array();
                    if (isset($format[1]))
                    {
                        $dateFormat = ' format="'.$format[1].'"';
                    }
                    $fieldString = '<field name="F'. $field->id. '"'.
                        ' type="calendar"'.
                        ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
                        $dateFormat .
                        $required .
                        '/>';
                    $fieldXml = new SimpleXMLElement($fieldString);
                    $form->setField($fieldXml);
                    break;
	            case 'signature' :
	            	$canvasWidth = (isset($field->canvasWidth)) ? $field->canvasWidth : 280;
		            $canvasHeight = (isset($field->canvasHeight)) ? $field->canvasHeight : 280;
		            $fieldString = '<field name="F'. $field->id. '"'.
			            ' type="signature"'.
			            ' label="'.htmlspecialchars($field->label,ENT_COMPAT, 'UTF-8'). '"'.
			            ' canvasWidth= "' . $canvasWidth . '"' .
			            ' canvasHeight= "' . $canvasHeight . '"' .
			            ' />';
		            $fieldXml = new SimpleXMLElement($fieldString);
		            $form->setField($fieldXml);
		            break;
                default:
                    break;
            }
            unset($fieldString);
            unset($fieldXml);
            unset($field);

        }
        $data = $this->loadFormData();
        $form->bind($data);
		return $form;
	}

    protected function loadFormData() {
        // check the session for previously entered form data
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_visforms.edit.visdata.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    protected function loadFormFieldsParameters() {
        $item = $this->item;
        $fields = $this->fieldDefinition;
        foreach ($fields as $field) {
            $key = 'F'. $field->id;
            switch($field->typefield) {
                case 'select':
                case 'multicheckbox' :
                    if (!empty($item->$key)) {
                        $item->$key = JHtmlVisformsselect::explodeMsDbValue($item->$key);
                    }
                    break;
                default:
                    break;
            }
            unset($key);
            unset($field);
        }
    }

    protected function extractDefaultValueParams($field) {
        foreach ($field->defaultvalue as $name => $value) {
            // make names shorter and set all default values as properties of field object
            $prefix =  'f_' . $field->typefield . '_';
            if (strpos($name, $prefix) !== false) {
                 $key = str_replace($prefix, "", $name);
                 $field->$key = $value;
            }
         }
         // delete default value array
         unset($field->defaultvalue);
         return $field;
    }

    protected function canDoTask($task) {
	    $user = JFactory::getUser();
	    $fid = JFactory::getApplication()->input->getInt('fid', -1);
	    // check form settings
	    if ($fid != -1) {
		    return $user->authorise($task, 'com_visforms.visform.' . (int) $fid);
	    }
	    else {
		    // Default to component settings
		    return $user->authorise($task, 'com_visforms');
	    }
    }
    
    protected function canEditState($record) {
		return $this->canDoTask('core.edit.data.state');
	}
	
    protected function canDelete($record) {
		return $this->canDoTask('core.delete.data');
	}
    
    public function setIsmfd($id, $state = true) {
        $table = $this->getTable();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName($table->getTableName('name')))
            ->set($db->quoteName('ismfd') . ' = ' . $state )
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        $db->execute();
    }
    
    public function restoreToUserInputs($id) {
        if ($this->checkIsmfd ($id)) {
            $table = $this->getTable();
            $tableName = $table->getTableName('name');
            $saveTableName = $tableName . "_save";
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName($saveTableName))
                ->where($db->quoteName('mfd_id') . ' = ' . $id);
            $db->setQuery($query);
            if ($orgData = $db->loadObject()) {
                $orgData->id = $id;
                $fid = JFactory::getApplication()->input->get('fid', 0, 'int');
                $this->copyFiles($fid, $orgData, true);
                $this->deleteFiles(Joomla\Utilities\ArrayHelper::fromObject($orgData), true);
                unset($orgData->mfd_id);
                unset($orgData->published);
                $orgData->ismfd = false;
                $orgData->modified = '0000-00-00 00:00:00';
                $orgData->modified_by = 0;
                $db->updateObject($tableName, $orgData, 'id', true);
            }
        }
    }
    
    public function copyOrgData($data) {
        $id = $data['id'];
        $isMfd = false;
        $table = $this->getTable();
        $tableName = $table->getTableName('name');
        $saveTableName = $tableName . "_save";
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName($tableName))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        if ($orgData = $db->loadObject()) {
            foreach ($data as $dataName => $dataValue) {
                // only real form field can be modified not the overhead fields. Field name of form fields in data table starts with "F"
                if (($dataName === "" || strpos($dataName, "F") === 0) && ($dataValue !== $orgData->$dataName)) {
                    $isMfd = true;
                    break;
                }
            }
            if (($isMfd == true) && ($orgData->ismfd == false)) {
                // recordset is modified for the first time. We save the original user inputs in the save-table
                // move uploaded files to a save directory im necessary
                $fid = JFactory::getApplication()->input->getInt('fid', -1);
                $this->copyFiles($fid, $orgData);
                unset($orgData->id);
                $orgData->mfd_id = $id;
                $orgData->checked_out = 0;
                $orgData->checked_out_time = '0000-00-00 00:00:00';
	            unset($orgData->modified);
	            unset($orgData->modified_by);
                unset($orgData->ismfd);
                $db->insertObject($saveTableName, $orgData);
            }
        }
        return $isMfd;
    }
    
    public function deleteOrgData($id) {
        $table = $this->getTable();
        $saveTableName = $table->getTableName('name') . "_save";
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName($saveTableName))
            ->where($db->quoteName('mfd_id') . ' = ' . $id);
        try {
	        $db->setQuery($query);
            $db->execute();
        }
        catch (RuntimeException $e) {
	        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
           return false;
        }
        return true;
    }
    
    public function checkIsmfd ($id) {
        $table = $this->getTable();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('ismfd'))
            ->from($db->quoteName($table->getTableName('name')))
            ->where($db->quoteName('id') . ' = ' . $id);
        try {
	        $db->setQuery($query);
	        return $db->loadResult();
        }
        catch (RuntimeException $e) {
	        return false;
        }
    }

    public function processDbValues($data) {
        $fields = $this->fieldDefinition;
        foreach ($fields as $field) {
            $key = 'F'.$field->id;
            if ((!empty($field->typefield)) && (in_array($field->typefield, array('select', 'multicheckbox')))) {
                $key = 'F'.$field->id;
                if ((!empty($data[$key])) && (is_array($data[$key]))) {
                    $dbValue = implode(JHtmlVisformsselect::$msdbseparator, $data[$key]);
                    $data[$key] = $dbValue;
                }
            }
            // checkbox is checked, safe correct value
            if ((!empty($field->typefield)) && (in_array($field->typefield, array('checkbox')))) {
                if ((is_array($data)) && (array_key_exists($key, $data)) && (!empty($field->attribute_value))) {
                    $data[$key] = $field->attribute_value;
                }
            }
            // inputs of type checkbox are no submitted with the post, if they are not checked we have to add an empty value manually
            if ((!empty($field->typefield)) && (in_array($field->typefield, array('checkbox', 'multicheckbox')))) {
                if ((is_array($data)) && (!array_key_exists($key, $data))) {
                    $data[$key] = "";
                }
            }
            unset($key);
            unset($field);
        }
        return $data;
    }
    
    public function uploadFiles($data) {
        $fields = $this->fieldDefinition;
        $input = JFactory::getApplication()->input;
        $fid = $input->get('fid', 0, 'int');
        $formModel = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
        $visform = $formModel->getItem($fid);
        $folder = $visform->uploadpath;
        $uploadFields = array();
        foreach ($fields as $field) {
            $key = 'F'.$field->id;
            if ((!empty($field->typefield)) && ($field->typefield == 'file')) {
                //we have to check if a new file was selected and needs upload
                $uploadField = new stdClass();
                $uploadField->name = $key;
                $uploadField->typefield = 'file';
                $uploadFields[] = $uploadField;
            }
            unset($key);
            unset($field);
            unset($uploadField);
        }
        if (!empty($uploadFields)) {
            $visform->fields = $uploadFields;
            try {
                $uploadsuccess = VisformsmediaHelper::uploadFiles($visform, 'admin');
            }
            catch (RuntimeException $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
            
            foreach ($visform->fields as $uploadField) {
                // set database value to empty if the file was marked as "to delete"
                $deleteFlagId = $uploadField->name. '-filedelete';
                if (!empty($data[$deleteFlagId])) {
                    $data[$uploadField->name] = "";
                }
                // store path and file information in database if a new file was uploaded
                if (!empty($uploadField->file['new_name'])) {
                    $file = new stdClass();
                     $file->folder = $folder;
                     $file->file = $uploadField->file['new_name'];
                     $registry = new JRegistry($file);
                     $data[$uploadField->name] = $registry->toString();
                }
                unset($uploadField);
            }
        }
		return $data;
	}
    
    public function deleteFiles($data, $restore = false) {
        if (empty($data) || (!is_array($data))) {
            return false;
        }
        if ((empty($this->fieldDefinition)) || (!is_array($this->fieldDefinition))) {
            return false;
        }
        $item = $this->getItem($data['id']);
        if (empty($item)) {
            return $data;
        }
        foreach ($this->fieldDefinition as $fieldDefinition) {
            $deleteFlagId = "F" . $fieldDefinition->id. '-filedelete';
            $fieldKey = "F" . $fieldDefinition->id;
            if (((empty($restore)) && (!empty($data[$deleteFlagId])) && ($data[$deleteFlagId] == 'delete') && ($fieldDefinition->typefield == 'file'))
                || ((!empty($restore)) && ($data[$fieldKey] != $item->$fieldKey) && ($fieldDefinition->typefield == 'file')))
            {
                $path = JHtml::_('visforms.getUploadFilePath', $item->$fieldKey);
                if (!empty($path)) {
                    VisformsmediaHelper::deletefile($path);
                }                
            }
        }
        return $data;
    }
    
    // if restore is set to true, we restsore original data and move file from the save folder to the original folder
    private function copyFiles($formId, $data, $restore = false) {
        if ((empty($formId)) || (empty($data)) || empty($data->id)) {
            return true;
        }
        if ((empty($this->fieldDefinition)) || (!is_array($this->fieldDefinition))) {
            return false;
        }
        foreach ($this->fieldDefinition as $fieldDefinition) {
            if ($fieldDefinition->typefield == 'file') {
                $fieldKey = "F" . $fieldDefinition->id;
                $filename = JHtml::_('visforms.getUploadFileName', $data->$fieldKey);
                $path = JHtml::_('visforms.getUploadFilePath', $data->$fieldKey);
                if ((!empty($path)) && (!empty($filename))) {
                    VisformsmediaHelper::copyfile($filename, $path, $restore);
                }
            }
        }
    }
}