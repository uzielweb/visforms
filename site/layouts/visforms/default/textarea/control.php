<?php
/**
 * Visforms control html of textarea field for default layout
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
        //We inclose textareas with HTML-Editor that are not readonly in a div
        if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor)
        {
             $html[] = '<div class="editor">';
        }
        $html[] = '<textarea ';
        if (!empty($field->attributeArray)) 
        {
             //add all attributes
             $html[] = ArrayHelper::toString($field->attributeArray, '=',' ', true);
        } 

        $html[] =  '>';
        $html[] = $field->initvalue;
        $html[] ='</textarea>';
        //field is a textarea with html Editor we have to close the div
        if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor) 
        {
          $html[] = '</div>';
        }
        echo implode('', $html);
    endif;  
endif;
?>
   
