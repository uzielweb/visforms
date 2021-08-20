<?php
/**
 * Visforms view for Visforms
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

class VisformsViewVisforms extends VisFormsItemsViewBase
{
	public $update_message;
	public $hasPdf;
	public $datasModel;
	public $pdfsModel;

    function __construct($config = array()) {
        parent::__construct($config);
        $this->viewName     = 'visforms';
        $this->editViewName = 'visform';
	    $this->hasPdf       = VisformsAEF::checkAEF(VisformsAEF::$pdf);
    }

    protected function setMembers() {
        $this->canDo = VisformsHelper::getActions();

        // show update message once
        $this->update_message = $this->app->getUserState('com_visforms.update_message');
        if (isset($this->update_message)) {
            $this->appsetUserState('com_visforms.update_message', null);
        }
        // load datas model
	    $this->datasModel = JModelLegacy::getInstance('Visdatas', 'VisformsModel', array('ignore_request' => true));
        // load pdfs model: get pdfs total of each form
	    if($this->hasPdf) {
		    $this->pdfsModel = JModelLegacy::getInstance('Vispdfs', 'VisformsModel', array('ignore_request' => true));
	    }
    }

    protected function getTitle() {
        return JText::_('COM_VISFORMS_SUBMENU_FORMS');
    }

	protected function setToolbar() {
		if ($this->canDo->get('core.create')) {
            JToolbarHelper::addNew('visform.add');
		}

		if ($this->canDo->get('core.edit.state')) {
            JToolbarHelper::publishList('visforms.publish');
			JToolbarHelper::unpublishList('visforms.unpublish');
			JToolbarHelper::checkin('visforms.checkin');
		}

		if ($this->canDo->get('core.delete')) {
            JToolbarHelper::deleteList('COM_VISFORMS_DELETE_FORM_TRUE', 'visforms.delete', 'COM_VISFORMS_DELETE');
		}

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
            JToolbarHelper::editList('visform.edit');
		}

		// add a batch button
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
			$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
			if ($hasSub && $this->user->authorise('core.create', 'com_visforms')) {
				// add export button that requires at least one item selected
				$message = "alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
				$dhtml = '<button data-toggle="modal" class="btn btn-small" onclick="if (document.adminForm.boxchecked.value==0){'.$message.'}else{jQuery( \'#exportFormModal\' ).modal(\'show\'); return true;}">
				<span class="icon-drawer" title="' . $title . '"></span> ' . JText::_('COM_VISFORMS_EXPORT_FORM_DEFINITION') . '</button>';
				$bar = JToolbar::getInstance('toolbar');
				$bar->appendButton('Custom', $dhtml, 'COM_VISFORMS_EXPORT_FORM_DEFINITION');
				// add modal button import file selection
				JToolbarHelper::modal('importFormModal', 'icon-file', 'COM_VISFORMS_IMPORT_FORM_DEFINITION');
			}
		}
	}

	// implementation

	public function getPdfsTotal($fid) {
    	if(isset($this->pdfsModel)) {
		    return $this->pdfsModel->getItemsTotal($fid);
	    }
	    return 0;
	}

	public function getDatasTotal($fid) {
    	if(isset($this->datasModel)) {
		    return $this->datasModel->getItemsTotal($fid);
	    }
	    return 0;
	}
}
