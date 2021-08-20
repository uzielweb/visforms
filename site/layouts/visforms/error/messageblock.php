<?php
/**
 * Visforms error html  for bootstrap default layout
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
    if (isset($displayData['errormessages'])) :
        $errormessages = $displayData['errormessages'];
        $html = array();
        $html[] = '<div class="alert alert-danger">';
        $html[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
        if (!empty($displayData['context'])) {
            switch($displayData['context']) {
                case 'dataeditform':
                    $html[] = '<h4 class="alert-heading">' . JText::_('COM_VISFORMS_EDIT_FORM_HAS_ERROR') . '</h4>';
                    break;
                default:
                    $html[] = '<h4 class="alert-heading">' . JText::_('COM_VISFORMS_FORM_HAS_ERROR') . '</h4>';
                    break;
            }
        } else {
            $html[] = '<h4 class="alert-heading">' . JText::_('COM_VISFORMS_FORM_HAS_ERROR') . '</h4>';
        }
        
        foreach ($errormessages as $errormessage) {
            $html[] = '<p>' . $errormessage . '</p>';
        }
        $html[] = '</div>';
        echo implode('', $html);
    endif;  
endif; ?>

        