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
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');

class VisFormsItemsViewBase extends JViewLegacy
{
    // framework
    public $app;
    public $doc;
    public $input;
    public $user;
    public $userId;
    public $listOrdering;
    public $listDirection;
    // component names
    public $baseName        = 'visforms';
    public $componentName   = 'com_visforms';
    public $authoriseName   = 'com_visforms.visform';
    public $viewName;
    public $editViewName;
    public $baseUrl;
    // payload
    public $fid;
    public $items;
    public $state;
    public $filterForm;
    public $activeFilters;
    public $pagination;
    public $sidebar;
    public $canDo;

    function __construct($config = array()) {
        parent::__construct($config);
        // framework
        $this->app          = JFactory::getApplication();
        $this->doc          = JFactory::getDocument();
        $this->input        = $this->app->input;
        $this->user		    = JFactory::getUser();
        $this->userId		= $this->user->get('id');
        // component names
        $this->baseUrl      = "index.php?option=$this->componentName";
        // payload
        $this->fid          = $this->getFIdFromInput();
    }

    public function display($tpl = null) {
        $this->setMembers();
        // get data from the model
        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination    = $this->get('Pagination');

        $this->listOrdering	 = $this->escape($this->state->get('list.ordering'));
        $this->listDirection = $this->escape($this->state->get('list.direction'));

        // we don't need toolbar in the modal window

        if (($this->getLayout() !== 'modal') && ($this->getLayout() !== 'modal_data')) {
            VisformsHelper::addSubmenu($this->viewName, $this->fid, $this->getSaveResultState());
            $this->sidebar = JHtmlSidebar::render();
            VisformsHelper::showTitleWithPreFix($this->getFormAddTitle());
            $this->setToolbar();
        }

        $this->addHeaderDeclarations();
        VisformsHelper::addCommonViewStyleCss();

        parent::display($tpl);
    }

    // overwrites: template methods

    protected function setMembers() { }

    protected function getTitle() { }

    protected function setToolbar() { }

    protected function addHeaderDeclarations() { }

    protected function getSaveResultState() {
    	return false;
    }

    // overwrites: internal

    protected function getFIdUrlQueryName() {
        return 'fid';
    }

    // implementation

    private function getFIdFromInput() {
        $name = $this->getFIdUrlQueryName();
        return $this->input->getInt($name, -1);
    }

    public function getSortHeader($text, $field) {
        return JHtml::_('searchtools.sort', $text, $field, $this->listDirection, $this->listOrdering);
    }

    protected function getFormAddTitle() {
    	if('visfields' == $this->viewName) {
		    $formTitle = $this->get('Formtitle');
	    }
	    else {
    		$fieldsModel = JModelLegacy::getInstance('Visfields', 'VisformsModel');
		    $formTitle = $fieldsModel->getFormtitle();
	    }
	    if( !empty($formTitle)) {
		    $formTitle = ' ' . JText::_('COM_VISFORMS_OF_FORM_PLAIN') . ' ' . $formTitle;
	    }
	    return $this->getTitle() . $formTitle;
    }
    
    protected function setItemsToolbar($deleteMessage = '') {
    	// local shortcuts
	    $viewName     = $this->viewName;
	    $editViewName = $this->editViewName;
	    
	    if ($this->canDo->get('core.create')) {
		    JToolbarHelper::addNew($editViewName.'.add');
	    }

	    if ($this->canDo->get('core.edit.state')) {
		    JToolbarHelper::publishList($viewName.'.publish');
		    JToolbarHelper::unpublishList($viewName.'.unpublish');
		    JToolbarHelper::checkin($viewName.'.checkin');
	    }

	    if ($this->canDo->get('core.delete')) {
		    JToolbarHelper::deleteList($deleteMessage, $viewName.'.delete', 'COM_VISFORMS_DELETE');
	    }

	    if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
		    JToolbarHelper::editList($editViewName.'.edit');
	    }

	    // Add a batch button
	    if ($this->user->authorise('core.create', 'com_visforms')
		    && $this->user->authorise('core.edit', 'com_visforms')
		    && $this->user->authorise('core.edit.state', 'com_visforms'))
	    {
		    JHtml::_('bootstrap.modal', 'collapseModal');
		    $title = JText::_('JTOOLBAR_BATCH');
		    // Instantiate a new JLayoutFile instance and render the batch button
		    $layout = new JLayoutFile('joomla.toolbar.batch');
		    $html = $layout->render(array('title' => $title));
		    // Get the toolbar object instance
		    $bar = JToolBar::getInstance('toolbar');
		    $bar->appendButton('Custom', $html, 'batch');
	    }

	    // navigation to forms and form is done via fields model functions
	    JToolbarHelper::custom('visfields.forms','forms','forms','COM_VISFORMS_SUBMENU_FORMS', false) ;
	    JToolbarHelper::custom('visfields.form','file-2','file2-','COM_VISFORMS_BACK_TO_FORM', false) ;
    }
}