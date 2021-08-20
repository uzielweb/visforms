<?php
/**
 * Default controller for Visforms
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

/**
 * Default controller class for Visforms
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */

class VisformsController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'viscpanel';

	public function __construct(array $config)
	{
		parent::__construct($config);
		//make sure that English language files are always loaded, so that missings translations are taken from the English language files
		$language = JFactory::getLanguage();
		$language->load('com_visforms', JPATH_ROOT . '/administrator/components/com_visforms' , 'en-GB', true);
		$language->load('com_visforms', JPATH_ROOT . '/administrator/components/com_visforms' , null, true);
		$language->load('com_visforms', JPATH_ROOT . '/administrator' , 'en-GB', true);
        $language->load('com_visforms', JPATH_ROOT . '/administrator' , null, true);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController          This object to support chaining.
	 *
	 * @since	1.6
	 */
	public function display($cachable = false, $urlparams = false)
	{
        //get Input from Request
        $visformsInput = JFactory::getApplication()->input;
        
        $view	= $visformsInput->get('view', 'viscpanel');
        $layout = $visformsInput->get('layout', 'default');
        $id		= $visformsInput->get('id');
        $fid     = $visformsInput->get('fid', 0);
        $canDo = VisformsHelper::getActions();
		
		// Check for edit form for forms.
		if ($view == 'visform' && $layout == 'edit' && !$this->checkEditId('com_visforms.edit.visform', $id)
		) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms', false));

			return false;
		}
		
		// Check for edit form for fields.
		if ($view == 'visfield' && $layout == 'edit' && !$this->checkEditId('com_visforms.edit.visfield', $id)
		) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visfields&fid=' . $fid, false));

			return false;
		}
        
        // Check for edit form for datas
        if ($view == 'visdata' && $layout == 'edit' && !$this->checkEditId('com_visforms.edit.visdata', $id)
		) {
			// Somehow the person just went to the form - we don't allow that.
	        $this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visdatas&fid=' . $fid, false));

			return false;
		}

		// Check for edit form for pdftemplates
		if ($view == 'vispdf' && $layout == 'edit' && !$this->checkEditId('com_visforms.edit.vispdf', $id)
		) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=vispdfs&fid=' . $fid, false));

			return false;
		}

		// Check for Core Create ACL for creator
		if ($view == 'creator' && !$canDo->get('core.edit.state')
		) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_visforms', false));

			return false;
		}
		
		parent::display($cachable, $urlparams);
		return $this;
	}
}

?>
