<?php
/**
 * Visforms view for Visforms
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.parameter');

/**
 * Visforms View class
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsViewVisforms extends JViewLegacy
{
	protected $menu_params;
	protected $visforms;
	protected $formLink;
	protected $state;
	protected $return;
	protected $steps;
	protected $setFocus;
	protected $editLinks;
	protected $successMessage;

	function display($tpl = null) {
		$layout = $this->getLayout();
		$app = JFactory::getApplication();
		switch ($layout) {
			case 'message' :
				$this->visforms = $this->get('Form');
				$successMessage = $app->getUserState('com_visforms.messages.' . $this->visforms->context, '');
				JPluginHelper::importPlugin('content');
				$this->successMessage = (!empty($successMessage)) ? JHtml::_('content.prepare', $successMessage) : $successMessage;
				$app->setUserState('com_visforms.messages.' . $this->visforms->context, null);
				$app->setUserState('com_visforms.' . $this->visforms->context, null);
				break;
			default:
				$this->_models['visforms']->addSupportedFieldType('pagebreak');
				$this->menu_params = $this->get('menuparams', 'visforms');
				$this->visforms = $this->get('Form');
				$this->return = JHtmlVisforms::base64_url_encode(JUri::getInstance()->toString());
				if (empty($this->visforms)) {
					$app->enqueueMessage(JText::_('COM_VISFORMS_FORM_MISSING') , 'error');
					return;
				}

				//check if user access level allows view
				$user = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				$access = (isset($this->visforms->access) && in_array($this->visforms->access, $groups)) ? true : false;
				if ($access == false) {
					$app->setUserState('com_visforms.' . $this->visforms->context . '.fields', null);
					$app->setUserState('com_visforms.' . $this->visforms->context, null);
					$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR') , 'error');
					return;
				}

				$this->canDo = VisformsHelper::getActions($this->visforms->id);
				if ((!empty($this->visforms->redirecttoeditview)) && (!empty($this->visforms->dataEditMenuExists))) {
					if ($this->canDo->get('core.edit.own.data')) {
						$datas = $this->get('Records');
						if (!empty($datas)) {
							$this->editLinks = array();
							foreach ($datas as $data) {
								if ((is_object($data)) && (!empty($data)) && !empty($data->id) && isset($data->published)) {
									if (!empty($data->published) || ($this->canDo->get('core.edit.data.state'))) {
										$this->editLinks[] = (int) $data->id;
									}
								}
							}
						}
						if (!empty($this->editLinks)) {
							$this->_layout = 'editlink';
							$app->setUserState('com_visforms.' . $this->visforms->context, null);
							$this->prepareDocument();
							parent::display($tpl);
							return true;
						}
					}
				}
				$fields = $this->get('Fields');
				$successMessage = $app->getUserState('com_visforms.messages.' . $this->visforms->context, '');
				JPluginHelper::importPlugin('content');
				$this->successMessage = (!empty($successMessage)) ? JHtml::_('content.prepare', $successMessage) : $successMessage;
				$app->setUserState('com_visforms.messages.' . $this->visforms->context, null);
				$app->setUserState('com_visforms.' . $this->visforms->context, null);
				$this->visforms->fields = $fields;
				$this->steps = (!empty($this->visforms->steps)) ? (int) $this->visforms->steps : (int) 1;
				$this->visforms->parentFormId = 'visform' . $this->visforms->id;
				//Trigger onFormPrepare event
				JPluginHelper::importPlugin('visforms');
				$app->triggerEvent('onVisformsFormPrepare', array('com_visforms.form', $this->visforms, $this->menu_params));
				$this->formLink = "index.php?option=com_visforms&task=visforms.send&id=" . $this->visforms->id;
				$this->setFocus = ((!isset($this->visforms->setfocus)) || ((isset($this->visforms->setfocus)) && ($this->visforms->setfocus == 1))) ? true : false;
				$options = JHtmlVisforms::getLayoutOptions($this->visforms);
				//process form layout
				$olayout = VisformsLayout::getInstance($this->visforms->formlayout, $options);
				if (is_object($olayout)) {
					//add layout specific css
					$olayout->addCss();
				}
				break;
		}

		$this->prepareDocument();
		parent::display($tpl);
	}

	private function prepareDocument() {
		$app = JFactory::getApplication();
		$title = '';
		if (isset($this->menu_params) && $this->menu_params->get('page_title')) {
			$title = $this->menu_params->get('page_title');
		}
		if ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		if ($title != '') {
			$this->document->setTitle($title);
		}
		// Set metadata Description and Keywords
		if (isset($this->menu_params) && $this->menu_params->get('menu-meta_description')) {
			$this->document->setDescription($this->menu_params->get('menu-meta_description'));
		}
		if (isset($this->menu_params) && $this->menu_params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->menu_params->get('menu-meta_keywords'));
		}
	}
}

?>
