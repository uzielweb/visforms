<?php
/**
 * Visfield view for Visforms
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
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemviewbase.php');

class VisformsViewVisfield extends VisFormsItemViewBase
{
    function __construct($config = array()) {
        parent::__construct($config);
        $this->editViewName = "visfield";
        $this->controllerName = 'visfield';
    }

	protected function setMembers() {
        $this->canDo = VisformsHelper::getActions($this->item->fid, $this->item->id);
        $data = $this->form->getData();
        $data->set('restrictions', $this->item->restrictions);
    }

    protected function getTitle() {
        $model = JModelLegacy::getInstance('Visfields', 'VisformsModel');
        $title = JText::_( 'COM_VISFORMS_FIELD' ) . ' ' . JText::_('COM_VISFORMS_OF_FORM_PLAIN') . ' ' . $model->getFormtitle();
        $text = $this->isNew ? JText::_( 'COM_VISFORMS_FIELD_NEW' ) : JText::_( 'COM_VISFORMS_FIELD_EDIT' );
        return $title . VisformsHelper::appendTitleAppendixFormat($text);
    }

    protected function setToolbar() {
        if ($this->canDo->get('core.create')) {
            JToolbarHelper::save2copy("$this->controllerName.save2copy");
        }
    }

    protected function addHeaderDeclarations() {
        $this->doc->addScript(JURI::root(true).'/administrator/components/com_visforms/js/visforms.js');
        $this->doc->addScript(JURI::root(true).'/administrator/components/com_visforms/js/jquery.csv.min.js');
    }
}
