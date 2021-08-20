<?php
/**
 * Created by PhpStorm.
 * User: aicha
 * Date: 06.03.2019
 * Time: 12:28
 */

defined('_JEXEC') or die( 'Restricted access' );

class VisformsItemControllerBase extends JControllerForm
{
	protected $app;
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->app = JFactory::getApplication();
		//$this->registerTask('showStepBadges', 'hideStepBadges');
	}

	protected function getAjaxRequestData() {
		// get the data
		$input = $this->app->input;
		$json = $input->get('data', '', 'raw');
		$data = json_decode($json);
		return $data;
	}

	protected function checkAjaxSessionToken() {
		$data = $this->getAjaxRequestData();
		$token = JSession::getFormToken();
		if ((!isset($data->$token)) || !((int) $data->$token === 1)) {
			return false;
		}
		return true;
	}
	// stored for later use
	/*public function hideStepBadges() {
		if ('hideStepBadges' === $this->getTask()) {
			$this->storeParam('hideStepBadges', 1);
		}
		else {
			$this->storeParam('hideStepBadges', 0);
		}
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visfields' . $this->getRedirectToListAppend(), false));
	}*/

	/*protected function storeParam($name, $value) {
		$component = JComponentHelper::getComponent('com_visforms');
		$component->params->set($name, $value);
		$componentId = $component->id;
		$table = JTable::getInstance('extension');
		$table->load($componentId);
		$table->bind(array('params' => $component->params->toString()));
		if (!$table->check()) {
			JFactory::getApplication()->enqueueMessage('Invalid params', 'error');
			return false;
		}
		if (!$table->store()) {
			JFactory::getApplication()->enqueueMessage('Problems saving params', 'error');
			return false;
		}
		return true;
	}*/

}