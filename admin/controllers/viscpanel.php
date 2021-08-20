<?php
/**
 * Visforms controller for VisCpanel
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class VisformsControllerViscpanel extends JControllerLegacy
{
    function __construct($config = array()){
        parent::__construct($config);
    }

    /**
     * display the visforms CSS
     *
     * @return void
     * @since Joomla 1.6
     */
    public function edit_css() {
        $this->setRedirect("index.php?option=com_visforms&task=vistools.editCSS");
    }

    public function dlid() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $dlId = $this->input->post->get('downloadid', '', 'string');
        $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=viscpanel', false));
        $model = $this->getModel('Viscpanel', 'VisformsModel');
        $model->setState('dlid', $dlId);
        if (!$model->storeDlid()) {
            return false;
        }
        return true;
    }

    public function gotSubUpdateInfo() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=viscpanel', false));
        $model = $this->getModel('Viscpanel', 'VisformsModel');
        if (!$model->storeGotSubUpdateInfo()) {
            return false;
        }
        return true;
    }

    public function installDemoForm() {
	    JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
	    $app = JFactory::getApplication();
	    $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=viscpanel', false));
	    $hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
	    if (!$hasSub || !JFactory::getUser()->authorise('core.create', 'com_visforms')) {
		    $app->enqueueMessage(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'), 'error');
	    	return false;
	    }

	    \JLoader::import('joomla.filesystem.file');
	    \JLoader::import('joomla.filesystem.folder');
	    \JLoader::import('joomla.filesystem.path');
	    $file = JPATH_ADMINISTRATOR . '/components/com_visforms/json/demoform.json';
	    if (!JFile::exists($file)) {
		    $app->enqueueMessage(JText::_('COM_VISFORMS_DEMOFORM_DEFINITION_FILE_MISSING'), 'error');
		    return false;
	    }
	    $jsonDefinition = @file_get_contents($file);
	    if (empty($jsonDefinition)) {
		    $app->enqueueMessage(JText::_('COM_VISFORMS_DEMOFORM_DEFINITION_FILE_EMPTY'), 'error');
		    return false;
	    }
	    $datas = json_decode($jsonDefinition, true);
	    if (empty($datas)) {
		    $app->enqueueMessage(JText::_('COM_VISFORMS_DEMOFORM_DEFINITION_INVALID'), 'error');
		    return false;
	    }
	    $helper = new visFormsImportHelper();
	    if ($helper->importForms($datas, true)) {
		    // create forms, fields and data records first
		    $cpanelModel = $this->getModel('Viscpanel', 'VisformsModel');
		    $cpanelModel->storeDemoFormInstalled();
		    $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms', false));
		    $app->enqueueMessage(JText::_('COM_VISFORMS_DEMOFORM_INSTALLED'), 'success');
		    return true;
	    }
	    return false;
    }
}