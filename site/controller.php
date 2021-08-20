<?php
/**
 * Visforms default controller
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
// ToDo remove on Joomla! 4
require_once JPATH_SITE . '/components/com_visforms/controllers/visforms.php';

class VisformsController extends JControllerLegacy
{
	public function __construct(array $config) {
		parent::__construct($config);
		//make sure that English language files are always loaded, so that missings translations are taken from the English language files
		$language = JFactory::getLanguage();
		$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', 'en-GB', true);
		$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', null, true);
		$language->load('com_visforms', JPATH_ROOT, 'en-GB', true);
		$language->load('com_visforms', JPATH_ROOT, null, true);
	}

	public function display($cachable = false, $urlparams = false) {
		$vName = $this->input->get('view', 'visforms');
		$this->input->set('view', $vName);
		if ($vName == 'visforms') {
			$app = JFactory::getApplication();
			$layout = $this->input->get('layout', 'default');
			$task = $this->input->getCmd('task');

			if ($layout == 'default' && !(isset($task))) {
				$model = $this->getModel('visforms');
				$model->addHits();
			}
			if ($layout == 'message') {
				//something to do?
			}
		}
		if ($vName == 'visformsdata') {
			$app = JFactory::getApplication();
			$cid = $this->input->getInt('cid');
			$this->input->set('view', 'visformsdata');
			$dataViewMenuItemExists = JHtmlVisforms::checkDataViewMenuItemExists($app->input->getInt('id', -1));
			//only display data list view with edit link if a menu item exists
			if (empty($dataViewMenuItemExists)) {
				$layout = $this->input->get('layout', 'data', 'string');
				if ($layout == 'dataeditlist' || $layout == 'detailedit') {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
					return false;
				}
			}
		}
		if ($vName == 'mysubmissions') {
			$menuexists = JHtmlVisforms::checkMySubmissionsMenuItemExists();
			if (empty($menuexists)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				return false;
			}
		}
		parent::display($cachable, $urlparams);
	}

	// ToDo remove on Joomla! 4
    public function captcha () {
        //legacy code for old version of vfformview plugin
        $controller = New VisformsControllerVisforms();
        $controller->execute('captcha');
        $controller->redirect();
    }
    
    public function send () {
        //legacy code for old version of vfformview plugin
        $controller = New VisformsControllerVisforms();
        $controller->execute('send');
        $controller->redirect();
    }
}
?>
