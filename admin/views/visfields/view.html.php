<?php
/**
 * Visfields view for Visforms
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
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemsviewbase.php');

class VisformsViewVisfields extends VisFormsItemsViewBase
{
	protected $form;

	function __construct($config = array()) {
        parent::__construct($config);
        $this->viewName     = 'visfields';
        $this->editViewName = 'visfield';
    }

	protected function setMembers() {
        $fid            = JFactory::getApplication()->input->getInt('fid', -1);
        $this->canDo    = VisformsHelper::getActions($fid);
        $this->form	    = $this->get('Form');
    }

    protected function getTitle() {
        return JText::_('COM_VISFORMS_FIELDS');
    }

    protected function setToolbar() {
        parent::setItemsToolbar('COM_VISFORMS_DELETE_FIELD_TRUE');
    }

	protected function getSaveResultState() {
		return $this->form->saveresult == '1';
	}
}
