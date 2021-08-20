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
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/itemviewbase.php');

/**
 * visforms View
 *
 * @package    Joomla.Administratoar
 * @subpackage com_visforms
 * @since      Joomla 1.6
 */
class VisformsViewVisdata extends VisFormsItemViewBase
{
	public $fields;

    function __construct($config = array()) {
        parent::__construct($config);
        $this->editViewName = "visdata";
        $this->controllerName = 'visdata';
    }

    protected function setMembers() {
        $this->fields = $this->get('Datafields');
        $this->canDo = VisformsHelper::getActions($this->fid);
        $this->canDoPostFix = '.data';
    }

    protected function getTitle() {
        $model = JModelLegacy::getInstance('Visfields', 'VisformsModel');
        $title = $model->getFormtitle();
        if( !empty($title)) {
            $title = ' ' . JText::_('COM_VISFORMS_OF_FORM_PLAIN') . ' ' . $title;
        }
        $text = JText::_('COM_VISFORMS_DATA_EDIT');
        return JText::_('COM_VISFORMS_VISFORM_DATA_RECORD_SET' ) . $title . VisformsHelper::appendTitleAppendixFormat($text);
    }

    protected function setToolbarNotCheckedOut() {
        if ($this->item->ismfd) {
            JToolbarHelper::custom("$this->controllerName.reset",'undo','undo','COM_VISFORMS_RESET_DATA', false) ;
        }
    }
}
