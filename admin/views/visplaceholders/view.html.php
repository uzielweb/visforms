<?php
/**
 * Visfields view for Visforms
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
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/views/visfields/view.html.php');

class VisformsViewVisplaceholders extends VisformsViewVisfields
{
	protected $form;

	function __construct($config = array()) {
        parent::__construct($config);
        $this->viewName     = 'visplaceholders';
    }
}
