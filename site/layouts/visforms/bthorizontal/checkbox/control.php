<?php
/**
 * Visforms control html for checkbox for bootstrap horizontal layout
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
        $html[] = '<div class="fc-tbx' . $field->errorId . '"></div>';
        //we wrap the control in a div if the field isCondtional, so that we can easily hide the whole control
        //the div class=control is part of the control because it's position divers, depending on the field type
        $html[] = '<div class="control-group ';
        $html[] = (isset($field->isConditional) && ($field->isConditional == true)) ? ' conditional field' . $field->id : 'field' . $field->id;
        $html[] = (isset($field->attribute_required) && ($field->attribute_required == true)) ? ' required' : '';
        //closing quote for class attribute
        $html[] = '"';
        $html[] = (isset($field->isDisabled) && ($field->isDisabled == true)) ? ' style="display:none;" ' : "";
        $html[] = '>';
        if (($ccustomtext != '') && (isset($field->customtextposition)) && (($field->customtextposition == 0) || ($field->customtextposition == 1)))
            {
                $html[] = '<div class="controls">';
                $html[] = $ccustomtext;
                $html[] = '</div>';
            }
            $html[] = $clabel;
        $html[] = '<div class="controls">';
        $html[] = '<label class="checkbox ' .$field->labelCSSclass . ' " id="' . $field->name. 'lbl" for="field'. $field->id . '">';
        $html[] = '<input ';

        if (!empty($field->attributeArray)) 
        {
                //add all attributes
                $html[] = ArrayHelper::toString($field->attributeArray, '=',' ', true);
        } 
        $html[] = '/>';
        $html[] = JHtml::_('visforms.createTip', $field);
        $html[] = "</label>";
        $html[] = '</div>';
         if (($ccustomtext != '') && (((isset($field->customtextposition)) && ($field->customtextposition == 2)) || !(isset($field->customtextposition))))
            {
                $html[] = '<div class="controls">';
                $html[] = $ccustomtext;
                $html[] = '</div>';
            }
        $html[] = '</div>';
        echo implode('', $html);
    endif;  
endif; ?>

        