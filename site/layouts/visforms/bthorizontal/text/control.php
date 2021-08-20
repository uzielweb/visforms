<?php
/**
 * Visforms control html for text field for bootstrap horizontal layout
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!empty($displayData)) : 
    if (isset($displayData['field'])) :
        $field = $displayData['field'];       
        //input
        $html = array();
        $html[] = '<input ';
        if (!empty($field->attributeArray)) 
        {
             //add all attributes
             $html[] = ArrayHelper::toString($field->attributeArray, '=',' ', true);
        } 

        $html[] =  '/>';
        echo implode('', $html);
    endif;  
endif; ?>