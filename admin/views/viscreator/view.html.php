<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 * @since        Joomla 3.6.2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemviewbase.php');

class VisFormsViewVisCreator extends VisFormsItemViewBase
{
	public $sidebar;
	public $typefield;

    protected function setMembers() {
        $this->editViewName   = "viscreator";
        $this->controllerName = 'viscreator';
        $this->hideButtons    = true;

	    // get creator typefield from field model
	    $fieldModel = JModelLegacy::getInstance('Visfield', 'VisformsModel', array('ignore_request' => true));
	    $fieldForm  = $fieldModel->getForm();
	    $this->typefield = $fieldForm->getField('typefield', '');
    }

    protected function getTitle() {
        return JText::_('COM_VISFORMS_FORM_CREATOR');
    }

    protected function setToolbar() {
    	// side bar
	    VisformsHelper::addSubmenu('viscreator');
	    $this->sidebar = JHtmlSidebar::render();
	    // tool bar
	    JToolbarHelper::link('javascript:visHelper.navigate(1);', 'COM_VISFORMS_CREATOR_BUTTON_OPEN_FORM', 'file');
	    JToolbarHelper::link('javascript:visHelper.navigate(2);', 'COM_VISFORMS_CREATOR_BUTTON_OPEN_FIELDS', 'forms');
	    JToolbarHelper::link('javascript:visHelper.navigate(3);', 'COM_VISFORMS_CREATOR_BUTTON_OPEN_PDF_TEMPLATES', 'file-2');
	    JToolbarHelper::link('javascript:visHelper.navigate(4);', 'COM_VISFORMS_CREATOR_BUTTON_OPEN_FORM_DATA', 'archive');
	    JToolbarHelper::link('javascript:visHelper.navigate(5);', 'COM_VISFORM_CREATOR_BUTTONS_CREATE_MAIN_MENU', 'home');
	    JToolbarHelper::link('javascript:visHelper.navigate(6);', 'COM_VISFORM_CREATOR_BUTTONS_CREATE_USER_MENU', 'user');
	    JToolbarHelper::link('javascript:visHelper.navigate(7);', 'COM_VISFORMS_RESET', 'unpublish');
	    //JToolbarHelper::link('javascript:visHelper.test();', 'Test', 'screen');
	    //JToolbarHelper::cancel("visform.cancel", 'COM_VISFORMS_CLOSE');
    }

	protected function setHideMainMenu() {
		JFactory::getApplication()->input->set('hidemainmenu', 0);
	}
}