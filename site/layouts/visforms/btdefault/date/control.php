<?php
/**
 * Visforms control html for date field for bootstrap default layout
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

if (!empty($displayData)) : 
    if (isset($displayData['field'])) :
        $field = $displayData['field'];
        $html = array();
        //date control is displayed in a div without usable css class (Joomla! core)
        //we inclose the control in a div class="asterix-ancor"
        if (isset($field->attribute_required) && ($field->attribute_required == 'required') && (isset($field->show_label) && ($field->show_label == 1))) 
        {
            $html[] = '<div class="asterix-ancor">';
        }
        //input
        $html[] = JHtml::_('visformscalendar.calendar', $field->attribute_value, $field->name, 'field' . $field->id, $field->dateFormatJs, $field->attributeArray);
        //close additional div
        if (isset($field->attribute_required) && ($field->attribute_required == 'required') && (isset($field->show_label) && ($field->show_label == 1)))
        {
            $html[] = '</div>';
        }
        echo implode('', $html);
    endif;  
endif; ?>