<?php
/**
 * Visfield table class
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/tables/tablebase.php');

class TableVisfield extends VisFormsTableBase
{	
	public function __construct(\JDatabaseDriver $db) {
		parent::__construct('#__visfields', 'id', $db);
	}

    protected function _getAssetName() {
        return 'com_visforms.visform.'. $this->fid . '.visfield.'.$this->id;
    }

    protected function _getAssetTitle() {
        return $this->label;
    }

    protected function _getAssetParentId(JTable $table = null, $id = null) {
        return $this->_getAssetFormId($table, $id);
    }

    public function bind($array, $ignore = '') {
        // bind the rules
        if (isset($array['rules'])) {
                $rules = new JAccessRules($array['rules']);
                $this->setRules($rules);
        }
        return parent::bind($array, $ignore);
	}

    function check() {
        if (empty($this->name)) {
            $this->name = "field-" . self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
        }

        // when we submit a form, fields are added to the $Request with the field name as request parameter name
        // if a field name matches a default request parameter (like id) this will be overridden with the user input for the form field
        // this can cause strange errors
        $forbiddenFieldNames = array('id', 'fid', 'view', 'task', 'option', 'lang', 'language', 'itemid', 'restrictions', 'return', 'creturn', 'tmpl', 'layout', 'format', 'extension', 'context', 'postid', 'addSupportedFieldType');
        foreach($forbiddenFieldNames as $fValue) {
            if($this->name == $fValue) {
                $this->name = "field-" . self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
                 JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_INVALID_FIELD_NAME_REPLACED',$fValue, $this->name), 'warning');
            }
        }

		// remove accented UTF-8 charachters in field name
		$this->name = JApplication::stringURLSafe($this->name, ENT_QUOTES);

		// set label
		if (empty($this->label)) {
			$this->label = $this->name;
		}

		// set ordering
		if (empty($this->ordering)) {
			// set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
		}
	    if (empty($this->dataordering)) {
		    // set ordering to last if ordering was 0
		    $this->dataordering = self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
	    }

		return true;
	}

	function store($updateNulls = false) {
		// verify that the field name is unique (we need that for proper form validation)
		$table = JTable::getInstance('Visfield', 'Table');
		if ($table->load(array('name' => $this->name, 'fid' => $this->fid))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_VISFORMS_UNIQUE_FIELD_NAME'));
			return false;
		}

		$this->addCreatedByFields();
		return parent::store($updateNulls = false);
	}
}