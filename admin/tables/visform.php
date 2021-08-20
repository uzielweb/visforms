<?php
/**
 * Visform table class
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

class TableVisform extends VisFormsTableBase
{
	public function __construct(\JDatabaseDriver $db) {
		$this->_jsonEncode = array('exportsettings','emailreceiptsettings','emailresultsettings', 'editemailreceiptsettings', 'editemailresultsettings', 'frontendsettings', 'layoutsettings', 'spamprotection', 'captchaoptions', 'viscaptchaoptions', 'savesettings', 'subredirectsettings');
		parent::__construct('#__visforms', 'id', $db);
	}

    protected function _getAssetName() {
        return 'com_visforms.visform.'.$this->id;
	}

    protected function _getAssetTitle() {
        return $this->title;
	}

    protected function _getAssetParentId(JTable $table = null, $id = null) {
        // we will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');
		// default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();
		// the item has the component as asset-parent
		$assetParent->loadByName('com_visforms');
		// return the found asset-parent-id
		if ($assetParent->id) {
			$assetParentId=$assetParent->id;
		}
		return $assetParentId;
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
		$return = true;
        if (empty($this->name)) {
            $this->name = "form_" . self::getNextOrder();
        }
		// remove accented UTF-8 characters in field name
		$this->name = JApplication::stringURLSafe($this->name, ENT_QUOTES);

		// set label
		if (empty($this->title)) {
            $this->title = $this->name;
		}
        
        // check upload directory
		JLoader::import('joomla.filesystem.folder');
        
        // convert backslashes to slashes
		$this->uploadpath = preg_replace('#\\\\#', '/', $this->uploadpath);
        // remove slashes at the beginning and the end of string
		$this->uploadpath = rtrim($this->uploadpath,'/');
        $this->uploadpath = ltrim($this->uploadpath,'/');
		$check = trim($this->uploadpath);
		if(!empty($check)) {
		    // todo: verify the code
            $check = JPath::clean($check);
            if(!JFolder::exists($this->uploadpath)) {
                $directory = JPATH_SITE.'/'.$this->uploadpath;
                if(!JFolder::exists($directory)) {
                    $this->setError(JText::_('COM_VISFORMS_DIRECTORY_DOESNT_EXISTS'));
                    $return = false;
                }
			}
		} 
		else {
            $this->setError(JText::_('COM_VISFORMS_DIRECTORY_EMPTY'));
			$return = false;
		}

		if ((!empty($this->emailresult)) && (empty($this->emailto))) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESULT_MAIL_TO_ADDRESS_REQUIRED', JText::_('COM_VISFORMS_FIELDSET_EMAIL')));
		}

		return $return;
	}
	
	public function store($updateNulls = false) {
        $this->addCreatedByFields();
		return parent::store($updateNulls);
	}
}