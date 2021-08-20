<?php
/**
 * Visforms control html for checkbox for default layout
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
        $clabel = $field->clabel;
        $ccustomtext = $field->ccustomtext;
        $html = array();
        $html[] = '<div class="';
        $html[] = (isset($field->isConditional) && ($field->isConditional == true)) ? 'conditional field' . $field->id : 'field' . $field->id;
        $html[] = (isset($field->attribute_required) && ($field->attribute_required == true)) ? ' required' : '';
        //closing quote for class attribute
        $html[] = '"';
        $html[] = (isset($field->isDisabled) && ($field->isDisabled == true)) ? ' style="display:none;" ' : "";
        $html[] = '>';
        //error container
        $html[] = '<div class="fc-tbx' . $field->errorId . '"></div>';
        if (($ccustomtext != '') && (isset($field->customtextposition)) && (($field->customtextposition == 0) || ($field->customtextposition == 1)))
        {
            $html[] = $ccustomtext;
        }
        //label
        $html[] = $clabel;
        //input
        $html[] = '<input ';
        if (!empty($field->attributeArray)) 
        {
             //add all attributes
             $html[] = ArrayHelper::toString($field->attributeArray, '=',' ', true);
        } 

        $html[] =  '/>';
        if (($ccustomtext != '') && (((isset($field->customtextposition)) && ($field->customtextposition == 2)) || !(isset($field->customtextposition))))
        {
            $html[] = $ccustomtext;
        }
        $html[] = '<p class="visCSSclear"><!-- --></p>';
        $html[] = '</div>';
        echo implode('', $html);
    endif;  
endif; ?>