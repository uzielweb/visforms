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

defined('_JEXEC') or die('Restricted access');

class VisFormsItemViewBase extends JViewLegacy
{
    // framework
    public $app;
    public $doc;
    public $input;
    public $user;
    public $userId;
    // component names
    public $baseName        = 'visforms';
    public $componentName   = 'com_visforms';
    public $editViewName;
    public $controllerName;
    public $baseUrl;
    // payload
    public $form;
    public $item;
    public $id;
    public $fid;
    public $canDo;
    public $canDoPostFix;
    public $isNew;
    public $checkedOut;
    public $cssName;
    public $hideButtons;
    public $badgesHidden;

    function __construct($config = array())
    {
        parent::__construct($config);
        // framework
        $this->app          = JFactory::getApplication();
        $this->doc          = JFactory::getDocument();
        $this->input        = $this->app->input;
        $this->user		    = JFactory::getUser();
        $this->userId	    = $this->user->get('id');
        // component names
        $this->baseUrl      = "index.php?option=$this->componentName";
        // defaults
	    $this->id          = 0;
	    $this->canDo       = VisformsHelper::getActions();
	    $this->isNew       = true;
	    $this->checkedOut  = false;
	    $this->hideButtons = false;
	    $component = JComponentHelper::getComponent('com_visforms');
	    $this->badgesHidden = $component->params->get('hideHelpBadges', '');
    }

    protected function initialize() {
        // payload
        $this->form		    = $this->get('Form');
        $this->item		    = $this->get('Item');
	    $this->fid          = $this->getFIdFromInput();
	    $this->canDoPostFix = '';
	    // item may not be available
	    if(isset($this->item)) {
		    if(isset($this->item->id)) {
			    $this->id    = (int) $this->item->id;
			    $this->canDo = VisformsHelper::getActions($this->item->id);
			    $this->isNew = ($this->item->id == 0);
		    }
		    if(isset($this->item->checkedOut)) {
			    $this->checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $this->userId);
		    }
        }
	    // derived class specific member initialization
        $this->setMembers();
    }

    public function display($tpl = null) {
        $this->initialize();
        VisformsHelper::showTitleWithPreFix($this->getTitle());

        if($this->hideButtons) {
	        $this->setToolbar();
        }
        else if ($this->isNew) {
	        if ($this->canDo->get('core.create')) {
	        	// ToDo show quick start help step in apply button, remove or complete
	        	//$layout = new JLayoutFile('div.quickstart_help_element');
	        	//$text =  $layout->render(array('step' => 2, 'tag' => 'span'));
		        //JToolbarHelper::apply("$this->controllerName.apply", JText::_('JTOOLBAR_APPLY') . ' ' . $text);
		        JToolbarHelper::apply("$this->controllerName.apply");
		        JToolbarHelper::save("$this->controllerName.save");
		        JToolbarHelper::save2new("$this->controllerName.save2new");
	        }
	        JToolbarHelper::cancel("$this->controllerName.cancel");
        }
        else {
	        // Can't save the record if it's checked out.
	        if (!$this->checkedOut) {
		        if ($this->canDo->get("core.edit$this->canDoPostFix") || ($this->canDo->get("core.edit.own$this->canDoPostFix") && $this->item->created_by == $this->userId)) {
			        JToolbarHelper::apply("$this->controllerName.apply");
			        JToolbarHelper::save("$this->controllerName.save");
			        $this->setToolbarNotCheckedOut();
		        }
	        }
	        $this->setToolbar();
	        JToolbarHelper::cancel("$this->controllerName.cancel", 'COM_VISFORMS_CLOSE');
        }

        $this->addHeaderDeclarations();
        VisformsHelper::addCommonViewStyleCss();
	    $this->setHideMainMenu();

        parent::display($tpl);
    }

    // overwrites: template methods

    protected function setMembers() { }

    protected function getTitle() { }

    protected function setToolbar() { }

    protected function addHeaderDeclarations() { }

    // overwrites: internal

    protected function getFIdUrlQueryName() {
        return 'fid';
    }

    protected function setToolbarNotCheckedOut() {
        if ($this->canDo->get('core.create')) {
            JToolbarHelper::save2new("$this->controllerName.save2new");
        }
    }

    protected function setHideMainMenu() {
	    JFactory::getApplication()->input->set('hidemainmenu', 1);
    }

    // implementation

    private function getFIdFromInput() {
        $name = $this->getFIdUrlQueryName();
        return $this->input->getInt($name, -1);
    }

    // stored for later use
	protected function addHideStepBadgesButtons() {
		if ($this->badgesHidden) {
			JToolbarHelper::custom("$this->controllerName.showStepBadges", 'help', 'help', 'COM_SHOW_STEP_BADGES', false);
		}
		else {
			JToolbarHelper::custom("$this->controllerName.hideStepBadges", 'help', 'help', 'COM_HIDE_STEP_BADGES', false);
		}
	}
}