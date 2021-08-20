<?php
/**
 * Visformsdata view for Visforms
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
defined('_JEXEC') or die( 'Restricted access' );

class VisformsViewVisformsdata extends JViewLegacy
{
	protected $form;
	protected $items;
	protected $state;
	protected $menu_params;
	protected $fields;
	protected $itemid;
	protected $canDo;
	protected $counterOffest;


	public function display($tpl = null) {
		$this->state = $this->get('State');
		//it is important to get the 'menu' params from the state because the params could be set by the plugin as well
		$this->menu_params = $this->state->get('params', new JRegistry);
		$this->form = $this->get('Form');
		$app = JFactory::getApplication();
		$isEditLayout = ($this->_layout == "detailedit" || $this->_layout == "dataeditlist") ? true : false;
		if (empty($this->form)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_DATAVIEW_FORM_MISSING'), 'error');
			return;
        }
        //visforms data views can be accessed without menu link. Allow visforms data views only if the form option is enabled
        //visforms data edit views can only be accessed if a menu item existes. The menu options control, which record sets can be viewed by a user
		if (empty($this->form->allowfedv) && empty($isEditLayout)) {
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return;
		}
         //check if user access level allows view
        $user = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
        $access = (isset($this->form->frontendaccess) && in_array($this->form->frontendaccess, $groups)) ? true : false;
		if ($access == false) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return;
		}
        $this->canDo = VisformsHelper::getActions($this->form->id);
        // get params from menu
        $title = '';
		if (isset($this->menu_params) && $this->menu_params->get('page_title')) {
            $title = $this->menu_params->get('page_title') ;
        }
		if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} 
		elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $this->document->setTitle($title);
		
		if ($this->menu_params['menu-meta_description']) {
			$this->document->setDescription($this->menu_params['menu-meta_description']);
		}

		if ($this->menu_params['menu-meta_keywords']) {
			$this->document->setMetadata('keywords', $this->menu_params['menu-meta_keywords']);
		}	
		//Item id
        $this->itemid = $this->state->get('itemid', '0');		
		//form id
		$this->id = JFactory::getApplication()->input->getInt('id', -1);

		// name of layout files for detail view must start with string detail
		if (strpos($this->_layout, 'detail') === 0) {
			// Get data from the model
			$this->item = $this->get('Detail');	
		} 
		
		// Get data from the model
		$this->items = $this->get('Items');		
        $this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->pagination = $this->get('Pagination');	
		$this->fields = $this->get('Datafields');
        $this->context = $this->get('Context');
        $this->counterOffest = $this->get('Start');
        JHtmlVisforms::includeScriptsOnlyOnce(array('visforms.min' => false, 'visdata.min' => true), array('validation' => false));
		
		parent::display($tpl);
		
	}
}
?>