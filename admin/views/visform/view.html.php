<?php
/**
 * Visform view for Visforms
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
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemviewbase.php');

class VisformsViewVisform extends VisFormsItemViewBase
{
    function __construct($config = array()) {
        parent::__construct($config);
        $this->editViewName = "visform";
        $this->controllerName = 'visform';
    }

    protected function setMembers() { }

    protected function getTitle() {
        $text = $this->isNew ? JText::_( 'COM_VISFORMS_FORM_NEW' ) : JText::_( 'COM_VISFORMS_FORM_EDIT' );
        return JText::_('COM_VISFORMS_FORM') . VisformsHelper::appendTitleAppendixFormat($text);
    }

    protected function setToolbar() {
    	// Todo remove quick start help step or complete it
	    //$layout = new JLayoutFile('div.quickstart_help_element');
	    //$text =  JText::_('COM_VISFORMS_FIELDS') .  ' ' . $layout->render(array('step' => 3, 'tag' => 'span'));
        if ($this->canDo->get('core.create')) {
            JToolbarHelper::save2copy("$this->controllerName.save2copy");
        }

        if (!$this->checkedOut) {
	        JToolbarHelper::custom("$this->controllerName.fields",'forms','forms','COM_VISFORMS_FIELDS',false) ;
        	//JToolbarHelper::custom("$this->controllerName.fields",'forms','forms',$text,false) ;
        }

        if ($this->form->getValue('saveresult') == '1') {
            JToolbarHelper::custom("$this->controllerName.datas",'archive','archive','COM_VISFORMS_DATAS',false) ;
        }

        $hasPdf = VisformsAEF::checkAEF(VisformsAEF::$pdf);
	    if ($hasPdf) {
		    JToolbarHelper::custom("$this->controllerName.pdfs",'file-2','file-2','COM_VISFORMS_PDFS',false) ;
	    }
    }

    protected function getFIdUrlQueryName() { return 'id'; }
}
