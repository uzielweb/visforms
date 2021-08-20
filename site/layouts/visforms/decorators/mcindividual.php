<?php
/**
 * Visforms layout specific html decoration for multi column layout
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
    if (isset($displayData['field']) && isset($displayData['clabel']) && isset($displayData['ccontrol']) && isset($displayData['ccustomtext'])) :
        //we wrap the control in a div if the field isCondtional, so that we can easily hide the whole control
        //the div class=control is part of the control because it's position divers, depending on the field type
        $field = $displayData['field'];
        $clabel = $displayData['clabel'];
        $ccontrol = $displayData['ccontrol'];
        $ccustomtext = $displayData['ccustomtext'];
        $bt_size = (isset($field->bootstrap_size) && ($field->bootstrap_size > 0)) ? 'span' . $field->bootstrap_size . ' ' : 'span6 ';
        $html = array();
        if (($clabel != "") || ($ccontrol != "") || ($ccustomtext != ""))
        {
            
            $html[] = '<div class="' . $bt_size;
            $html[] = (isset($field->isConditional) && ($field->isConditional == true)) ? 'conditional field' . $field->id : 'field' . $field->id;
            $html[] = (isset($field->attribute_required) && ($field->attribute_required == true)) ? ' required' : '';
            //closing quote for class attribute
            $html[] = '"';
            $html[] = (isset($field->isDisabled) && ($field->isDisabled == true)) ? ' style="display:none;" ' : "";
            $html[] = '>';
            if (($ccustomtext != '') && (isset($field->customtextposition)) && ($field->customtextposition == 0))
            {
                $html[] = $ccustomtext;
            }
            //$html[] = '<div>';
            $html[] = $clabel;
            if (($ccustomtext != '') && (isset($field->customtextposition)) && ($field->customtextposition == 1))
            {
                $html[] = $ccustomtext;
            }
            $html[] = $ccontrol;
	        $layout = new JLayoutFile('visforms.decorators.default_error_div', null);
	        $layout->setOptions(array('component' => 'com_visforms'));
	        $html[] = $layout->render($displayData);
            if (($ccustomtext != '') && (((isset($field->customtextposition)) && ($field->customtextposition == 2)) || !(isset($field->customtextposition))))
            {
                $html[] = $ccustomtext;
            }

            $html[] = '</div>';
        }
        echo implode('', $html);
    endif;  
endif; ?>