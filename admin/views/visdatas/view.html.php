<?php
/**
 * Visdata view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemsviewbase.php');

class VisformsViewVisdatas extends VisFormsItemsViewBase
{
    public $fields;

    function __construct($config = array()) {
        parent::__construct($config);
        $this->viewName     = 'visdatas';
        $this->editViewName = 'visdata';
	    $this->controllerName = 'visdatas';
    }

	protected function setMembers() {
        // todo: change implementation to default 0 everywhere including base class
        $this->fid = JFactory::getApplication()->input->getInt('fid', 0);
        $this->canDo    = VisformsHelper::getActions($this->fid);
        $this->fields   = $this->get('PublishedDatafields');
    }

    protected function getTitle() {
        return JText::_('COM_VISFORMS_VISFORM_DATA_RECORD_SETS');
    }

    protected function setToolbar()
	{
        if ($this->canDo->get('core.edit.state')) {
            JToolbarHelper::publishList("$this->controllerName.publish");
            JToolbarHelper::unpublishList("$this->controllerName.unpublish");
            JToolbarHelper::checkin("$this->controllerName.checkin");
        }

        if ($this->canDo->get('core.export.data')) {
            JToolbarHelper::custom("$this->controllerName.export",'export.png','export.png','COM_VISFORMS_EXPORT', false) ;
        }

        if ($this->canDo->get('core.delete.data')) {
            JToolbarHelper::deleteList('COM_VISFORMS_DELETE_DATASET_TRUE',"$this->controllerName.delete", 'COM_VISFORMS_DELETE');
        }

        if ($this->canDo->get('core.edit.data') || $this->canDo->get('core.edit.own.data')) {
            JToolbarHelper::editList('visdata.edit');
            JToolbarHelper::custom("$this->controllerName.reset",'undo','undo','COM_VISFORMS_RESET_DATA', true) ;
        }

        JToolbarHelper::custom("$this->controllerName.forms",'forms','forms','COM_VISFORMS_SUBMENU_FORMS', false) ;
        JToolbarHelper::custom("$this->controllerName.form",'file-2','file-2','COM_VISFORMS_BACK_TO_FORM', false) ;
	}

	protected function getSaveResultState() {
    	return true;
	}
}
