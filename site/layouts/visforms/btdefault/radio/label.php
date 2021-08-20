<?php
/**
 * Visforms label html for radio for bootstrap default layout
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
        $labelClass = $field->labelClass;
        $html = array();
        //label
        $html[] = '<label class=" '. $labelClass .$field->labelCSSclass . '" id="' . $field->name. 'lbl">';
        $html[] = JHtml::_('visforms.createTip', $field);
        $html[] = '</label>';
        echo implode('', $html);
    endif;  
endif; ?>