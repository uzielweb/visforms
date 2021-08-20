<?php
/**
 * Visforms label html for checkbox for bootstrap horizontal layout
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


//label is part of the control but we add a span which can take the required asterix if the checkbox is required
if (!empty($displayData)) : 
    if (isset($displayData['field'])) :
        $field = $displayData['field'];
        $labelClass = $field->labelClass;
        $html = array();
        //create an empty span that can take on the required asterix
        if (isset($field->attribute_required) && ($field->attribute_required == 'required')) 
        {
            $html[] = '<span class="' . $labelClass . '"></span>';
        }
        echo implode('', $html);
    endif;  
endif; ?>