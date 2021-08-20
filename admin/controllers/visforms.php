<?php
/**
 * visforms controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die( 'Restricted access' );

class VisformsControllerVisforms extends JControllerAdmin
{
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function getModel($name = 'Visform', $prefix = 'VisformsModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}