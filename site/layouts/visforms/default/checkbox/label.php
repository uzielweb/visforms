<?php
/**
 * Visforms label html for checkbox for default layout
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
        //label
        $html = array();
        $html[] = '<label class=" ' . $labelClass . ' ' .$field->labelCSSclass . '" id="' . $field->name. 'lbl" for="field' . $field->id .'">';
        $html[] = JHtml::_('visforms.createTip', $field);
        $html[] = '</label>';
        echo implode('', $html);
    endif;  
endif; ?>
        