<?php
/**
 * Visforms Layout class Bootstrap horizontal
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
require_once(__DIR__ . '/btdefault.php');

/**
 * Set properties of a form according to it's type and layout settings
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsLayoutBthorizontal extends VisformsLayoutBtdefault
{     
     /**
        * Method to create Custom css
        * Used for display and positioning of required asterix
        * @param string $fullParent id of enclosing form element
        * @return string css
        */
       protected function getCustomRequiredCss ($parent)
       {
           $fullParent = 'form#' . $parent;
           $css = array();
           //css for required inputs with placeholder instead of label and for required checkboxes
           $css[] = $fullParent . ' div.control-group.required span.control-label:before ';
           $css[] = '{content:"*"; color:red; display: inline-block; padding-right: 0;} ';
           //css for all other required fields
           $css[] = $fullParent . ' div.control-group.required .control-label:before ';
           $css[] = '{content:"*"; color:red; display: inline-block; padding-right: 10px;} ';
           return implode('', $css);
       }
}