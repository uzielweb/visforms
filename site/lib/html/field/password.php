<?php
/**
 * Visforms HTML class for password field
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
require_once(__DIR__ . '/text.php');

/**
 * Create HTML of a password field according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlPassword extends VisformsHtmlText
{
      /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     */
    public function __construct($field, $decorable, $attribute_type)
    {
        $attribute_type = "password";
        parent::__construct($field, $decorable, $attribute_type);
    }       
}