<?php
/**
 * viscpanel view for Visforms
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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * viscpanel view
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since        Joomla 1.6
 */
class VisformsViewViscpanel extends JViewLegacy
{
	protected $canDo;
	protected $infoLinksRoot;
	protected $linkLanguage;

	public function __construct(array $config) {
		parent::__construct($config);
		$language = JFactory::getLanguage();
		$this->language_tag = $language->getTag();
		$this->infoLinksRoot = 'https://www.vi-solutions.de';
		$this->setLinkLanguage();
	}

	function display($tpl = null) {
		VisformsHelper::addSubmenu('viscpanel');
		$this->sidebar = JHtmlSidebar::render();
		$this->canDo = VisformsHelper::getActions();
		$this->preferencesLink = $this->getPreferencesLink();
		$this->documentationLink = $this->getDocumentationLink();
		$this->forumLink = $this->getForumLink();
		$this->donateLink = $this->getDonateLink();
		$this->dlidFormLink = $this->getDlidFormLink();
		$this->versionCompareLink = $this->getVersionCompareLink();
		$this->buySubsLink = $this->getBuySubsLink();
		$this->dlidInfoLink = $this->dlidInfoLink();
		$this->translationsLink = $this->translationsLink();
		$this->gotSubUpdateInfoLink = $this->gotSubUpdateInfoLink();
		$this->subUpdateInstructionLink = $this->subUpdateInstructionLink();
		$this->subUpdateMoreInfoLink = $this->subUpdateMoreInfoLink();
		$app = JFactory::getApplication();
		$this->extUpdateMoreInfoLink = $this->extUpdateMoreInfoLink();
		// Do not show twitter link
		$this->twitterLink = '';//$this->twitterLink();
		$this->installPdfDemoFormLink = $this->gotInstallPdfDemoFormLink();
		$this->update_message = $app->getUserState('com_visforms.update_message');
		//only show update message once
		if (isset($this->update_message)) {
			$app->setUserState('com_visforms.update_message', null);
		}
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		VisformsHelper::addCommonViewStyleCss();
		JToolbarHelper::title(JText::_('COM_VISFORMS') . ' - ' . JText::_('COM_VISFORMS_SUBMENU_CPANEL_LABEL'), 'visform');
		// Options button.
		if (JFactory::getUser()->authorise('core.admin', 'com_visforms')) {
			JToolbarHelper::preferences('com_visforms');
		}
	}

	protected function getPreferencesLink() {
		$uri = (string) JUri::getInstance();
		$return = urlencode(base64_encode($uri));
		return 'index.php?option=com_config&amp;view=component&amp;component=com_visforms&amp;return=' . $return;
	}

	protected function setLinkLanguage() {
		if ($this->language_tag === "de-DE") {
			$this->linkLanguage = 'de';
		}
		else {
			$this->linkLanguage = 'en';
		}
	}

	protected function getEditcssLink() {
		$uri = (string) JUri::getInstance();
		$return = urlencode(base64_encode($uri));
		return 'index.php?option=com_visforms&amp;task=viscpanel.edit_css&amp;return=' . $return;
	}

	protected function getDocumentationLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=documentation&amp;lang=' . $this->linkLanguage;
	}

	protected function getForumLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=forum&amp;lang=' . $this->linkLanguage;
	}

	protected function getDonateLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=donate&amp;lang=' . $this->linkLanguage;
	}

	protected function getDlidFormLink() {
		return 'index.php?option=com_visforms&task=viscpanel.dlid';
	}

	protected function getVersionCompareLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=subinfo&amp;lang=' . $this->linkLanguage;
	}

	protected function getBuySubsLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=buysub&amp;lang=' . $this->linkLanguage;
	}

	protected function dlidInfoLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=dlidinfo&amp;lang=' . $this->linkLanguage;
	}

	protected function translationsLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=translation&amp;lang=' . $this->linkLanguage;
	}

	protected function gotSubUpdateInfoLink() {
		return 'index.php?option=com_visforms&task=viscpanel.gotSubUpdateInfo';
	}

	protected function subUpdateInstructionLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=subupdateinstruction&amp;lang=' . $this->linkLanguage;
	}

	protected function subUpdateMoreInfoLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=subupdatemoreinfo&amp;lang=' . $this->linkLanguage;
	}

	protected function extUpdateMoreInfoLink() {
		return $this->infoLinksRoot . '/index.php?option=com_vislinkrouter&amp;linktype=extupdatemoreinfo&amp;lang=' . $this->linkLanguage;
	}

	protected function twitterLink() {
		if (JFactory::getLanguage()->getTag() == 'de-DE') {
			return 'https://twitter.com/visForms';
		}
		return 'https://twitter.com/visForms_en';
	}

	protected function gotInstallPdfDemoFormLink() {
		return 'index.php?option=com_visforms&task=viscpanel.installDemoForm&'.JSession::getFormToken() . '=1';
	}
}