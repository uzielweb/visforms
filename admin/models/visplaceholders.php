<?php
/**
 * visfields model for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die();

require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/models/visfields.php');
class VisformsModelVisplaceholders extends VisformsModelVisfields
{
    protected $fid;

	public function __construct($config = array()) {
		parent::__construct($config);
        $app = JFactory::getApplication();
		$this->fid = $app->input->getInt('fid',  0);
	}
}