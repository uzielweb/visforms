<?php

/**
 * Visform table class
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/tables/tablebase.php');

class TableVisdata extends VisFormsTableBase
{   
    public function __construct(\JDatabaseDriver $db) {
        $id = JFactory::getApplication()->input->getInt('fid', -1);
        parent::__construct('#__visforms_' . $id, 'id', $db);
    }
}