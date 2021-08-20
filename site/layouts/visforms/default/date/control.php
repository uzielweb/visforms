<?php
/**
 * Visforms control html for date field for default layout
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
        //input
        $html = JHtml::_('visformscalendar.calendar', $field->attribute_value, $field->name, 'field' . $field->id, $field->dateFormatJs, $field->attributeArray);
        echo $html;
    endif;  
endif; ?>