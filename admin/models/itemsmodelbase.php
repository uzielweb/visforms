<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class VisFormsItemsModelBase extends JModelList {

	protected $fid;

	public function __construct($config = array()) {
		parent::__construct($config);
		$app = JFactory::getApplication();
		$this->fid = $app->input->getInt('fid',  0);
	}

	public function getForm() {
		$db	= $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__visforms'))
			->where('id='.$this->fid);
		$db->setQuery($query);
		$form = $db->loadObject();
		return $form;
	}
}